<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class FrontendOrderPaymentController extends Controller
{
    public function payBalance(Request $request, Order $order): View|RedirectResponse
    {
        // Simple signature check
        $expectedSignature = hash_hmac('sha256', $order->id, config('app.key'));
        if ($request->query('signature') !== $expectedSignature) {
            abort(403, 'Invalid signature');
        }

        $balance = $order->grand_total - $order->amount_paid;
        if ($balance <= 0) {
            return redirect()->route('frontend.home')->with('success', __('order_already_paid'));
        }

        $currency = Currency::where('code', $order->payment_currency)->first() ?: Currency::active()->first();
        $paymentMethods = $this->getGatewayPaymentMethods();

        return view('frontend.pay-balance', [
            'order' => $order,
            'balance' => $balance,
            'currency' => $currency,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function createBalancePaymentIntent(Request $request, Order $order): \Illuminate\Http\JsonResponse
    {
        $expectedSignature = hash_hmac('sha256', $order->id, config('app.key'));
        if ($request->header('X-Signature') !== $expectedSignature) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $balance = $order->grand_total - $order->amount_paid;
        if ($balance <= 0) {
            return response()->json(['message' => 'Order already paid'], 422);
        }

        $paymentMethodCode = $request->input('payment_method');
        $paymentMethod = $this->findActivePaymentMethod($paymentMethodCode);
        $credentials = $this->resolveGatewayCredentials($paymentMethod);
        
        $currency = Currency::where('code', $order->payment_currency)->first();
        $minorAmount = $this->toMinorAmount($balance, $currency);

        $response = Http::withBasicAuth($credentials['secret_key'], '')
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => $minorAmount,
                'currency' => strtolower($currency?->code ?: 'usd'),
                'payment_method_types[]' => 'card',
                'description' => 'Balance payment for order ' . ($order->order_number ?: $order->id),
                'receipt_email' => $order->email,
            ]);

        $payload = $response->json();
        if (!$response->successful()) {
            return response()->json(['message' => $payload['error']['message'] ?? 'Failed to create intent'], 422);
        }

        return response()->json([
            'client_secret' => $payload['client_secret'],
            'payment_intent_id' => $payload['id'],
        ]);
    }

    public function processBalancePayment(Request $request, Order $order): RedirectResponse
    {
        $balance = $order->grand_total - $order->amount_paid;
        $currency = Currency::where('code', $order->payment_currency)->first();
        
        $paymentMethod = $this->findActivePaymentMethod($validated['payment_method']);
        if (!$paymentMethod) {
            return back()->with('error', 'Invalid payment method');
        }

        try {
            if (($paymentMethod['type'] ?? '') === 'stripe') {
                $gatewayIntentId = $request->input('gateway_payment_intent_id');
                if (!$gatewayIntentId) {
                    throw new \Exception('Payment intent ID is required for Stripe');
                }
                
                $paymentVerification = $this->verifyGatewayPayment(
                    $paymentMethod,
                    $gatewayIntentId,
                    $balance,
                    $currency
                );

                $order->update([
                    'amount_paid' => $order->amount_paid + $balance,
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentVerification['reference'],
                    'payment_payload' => array_merge((array)$order->payment_payload, ['balance_payment' => $paymentVerification['payload']]),
                    'extra_amount_paid' => ($order->extra_amount_paid ?? 0) + $balance,
                ]);
            } else {
                // Manual payment (Bank Account, etc.)
                $order->update([
                    'payment_status' => 'pending_verification', // New status or existing? 
                    'payment_method' => $paymentMethod['code'],
                    'remarks' => ($order->remarks ? $order->remarks . "\n" : "") . "Customer requested balance payment via " . $paymentMethod['title'],
                    'extra_amount_paid' => null,
                ]);

                return redirect()->route('frontend.checkout.success')->with('frontend_checkout_success', true)->with('success', __('manual_payment_instruction_sent'));
            }

            return redirect()->route('frontend.checkout.success')->with('frontend_checkout_success', true);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function getGatewayPaymentMethods(): array
    {
        return collect(Setting::paymentMethods(true))
            ->map(function (array $method) {
                $isManual = in_array($method['type'] ?? '', ['cash_on_delivery', 'bank_account']);
                $credentials = !$isManual ? $this->resolveGatewayCredentials($method) : ['public_key' => null];
                
                return [
                    'code' => $method['code'],
                    'type' => $method['type'] ?? 'unknown',
                    'title' => $method['title'] ?? Str::headline(str_replace('_', ' ', $method['code'])),
                    'description' => $method['description'] ?? '',
                    'public_key' => $credentials['public_key'],
                    'is_manual' => $isManual,
                    'config' => $method['config'] ?? [],
                ];
            })
            ->values()
            ->all();
    }

    private function findActivePaymentMethod(string $code): ?array
    {
        return collect(Setting::paymentMethods(true))
            ->first(fn ($method) => is_array($method) && ($method['code'] ?? null) === $code);
    }

    private function resolveGatewayCredentials(array $method): array
    {
        $config = is_array($method['config'] ?? null) ? $method['config'] : [];
        $environment = ($config['environment'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';

        if (($method['type'] ?? null) === 'stripe') {
            return [
                'public_key' => $environment === 'live' ? trim((string)($config['live_public_key'] ?? '')) : trim((string)($config['sandbox_public_key'] ?? '')),
                'secret_key' => $environment === 'live' ? trim((string)($config['live_secret_key'] ?? '')) : trim((string)($config['sandbox_secret_key'] ?? '')),
            ];
        }
        return ['public_key' => null, 'secret_key' => null];
    }

    private function verifyGatewayPayment(array $paymentMethod, string $paymentIntentId, float $expectedTotal, ?Currency $currency): array
    {
        $credentials = $this->resolveGatewayCredentials($paymentMethod);
        $response = Http::withBasicAuth($credentials['secret_key'], '')
            ->get('https://api.stripe.com/v1/payment_intents/' . $paymentIntentId);

        $payload = $response->json();
        if (!$response->successful() || ($payload['status'] ?? '') !== 'succeeded') {
            throw new \Exception(__('payment_verification_failed'));
        }

        return [
            'status' => 'paid',
            'reference' => $payload['id'],
            'payload' => $payload,
        ];
    }
    private function toMinorAmount(float $baseAmount, ?Currency $currency): int
    {
        $rate = max((float) ($currency?->exchange_rate ?? 1), 1);
        $displayAmount = $baseAmount / $rate;
        $currencyCode = strtoupper($currency?->code ?: 'USD');
        $zeroDecimalCurrencies = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

        if (in_array($currencyCode, $zeroDecimalCurrencies, true)) {
            return (int) round($displayAmount);
        }

        return (int) round($displayAmount * 100);
    }
}
