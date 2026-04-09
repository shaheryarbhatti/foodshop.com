<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Tax;
use App\Services\BranchDeliveryService;
use App\Services\OrderPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FrontendCheckoutController extends Controller
{
    public function index(BranchDeliveryService $branchDeliveryService): View
    {
        $customer = null;
        if (session()->has('frontend_customer_id')) {
            $customer = Customer::find(session('frontend_customer_id'));
        }

        $currentCurrency = Currency::find(session('frontend_currency_id')) ?: Currency::active()->first() ?: Currency::query()->first();
        $deliverySummary = $branchDeliveryService->summarizeForCustomer($customer, 0, 'delivery');
        $countries = Country::where('status', true)->orderBy('name')->get();

        return view('frontend.checkout', [
            'customer' => $customer,
            'activeTax' => Tax::where('status', true)->orderBy('id')->first(),
            'currentCurrency' => $currentCurrency,
            'paymentMethods' => $this->checkoutPaymentMethods(),
            'countries' => $countries,
            'checkoutCountryCodes' => $countries
                ->filter(fn ($country) => filled($country->country_code))
                ->mapWithKeys(fn ($country) => [mb_strtolower($country->name) => strtoupper($country->country_code)])
                ->all(),
            'deliverySummary' => $deliverySummary,
            'mapProvider' => Setting::get('map_provider', 'leaflet'),
            'googleMapsApiKey' => Setting::get('google_maps_api_key', ''),
        ]);
    }

    public function deliverySummary(Request $request, BranchDeliveryService $branchDeliveryService): JsonResponse
    {
        $validated = $request->validate([
            'subtotal' => 'nullable|numeric|min:0',
            'order_type' => 'nullable|in:delivery,pick_up',
        ]);

        $customer = session()->has('frontend_customer_id')
            ? Customer::find(session('frontend_customer_id'))
            : null;

        return response()->json([
            'summary' => $branchDeliveryService->summarizeForCustomer(
                $customer,
                (float) ($validated['subtotal'] ?? 0),
                (string) ($validated['order_type'] ?? 'delivery')
            ),
        ]);
    }

    public function createPaymentIntent(Request $request, BranchDeliveryService $branchDeliveryService): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'required|string|max:40',
        'order_type' => 'required|in:delivery,pick_up',
        'payment_method' => 'required|string',
        'cart_data' => 'required|json',
    ]);

        $customer = session()->has('frontend_customer_id')
            ? Customer::find(session('frontend_customer_id'))
            : null;

        $paymentMethod = $this->findActivePaymentMethod($validated['payment_method']);
        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_unavailable_payment_method'),
            ]);
        }

        if (! $this->requiresGatewayCardForm($paymentMethod)) {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_payment_method_not_gateway'),
            ]);
        }

        $credentials = $this->resolveGatewayCredentials($paymentMethod);
        if (! $credentials['public_key'] || ! $credentials['secret_key']) {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_payment_method_missing_keys'),
            ]);
        }

        $cartItems = json_decode($validated['cart_data'], true);
        if (empty($cartItems) || ! is_array($cartItems)) {
            throw ValidationException::withMessages([
                'cart_data' => __('frontend_basket_empty'),
            ]);
        }

        $orderPricingService = app(OrderPricingService::class);
        $totals = $orderPricingService->calculate($cartItems, $validated['order_type'], $customer);
        
        $grandTotal = $totals['grand_total'];
        $currentCurrency = $this->resolveCurrentCurrency();
        $currencyCode = strtolower($currentCurrency?->code ?: 'usd');
        $minorAmount = $this->toMinorAmount($grandTotal, $currentCurrency);

        $response = Http::withBasicAuth($credentials['secret_key'], '')
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => $minorAmount,
                'currency' => $currencyCode,
                'payment_method_types[]' => 'card',
                'description' => 'Foodshop checkout payment',
                'receipt_email' => $validated['email'],
                'metadata[method_code]' => $paymentMethod['code'],
                'metadata[first_name]' => $validated['first_name'],
                'metadata[last_name]' => $validated['last_name'],
                'metadata[order_type]' => $validated['order_type'],
            ]);

        $payload = $response->json();
        if (! $response->successful() || empty($payload['client_secret'])) {
            return response()->json([
                'message' => $payload['error']['message'] ?? __('frontend_payment_failed_generic'),
            ], 422);
        }

        if (! empty($payload['id'])) {
            Log::info('Stripe payment intent created for checkout', [
                'payment_method_code' => $paymentMethod['code'],
                'payment_intent_id' => $payload['id'],
                'order_type' => $validated['order_type'],
                'grand_total' => $grandTotal,
            ]);

            session([
                'frontend_pending_payment_intent_id' => $payload['id'],
                'frontend_pending_payment_method_code' => $paymentMethod['code'],
            ]);
        }

        return response()->json([
            'client_secret' => $payload['client_secret'],
            'payment_intent_id' => $payload['id'] ?? null,
        ]);
    }

    public function submit(Request $request, BranchDeliveryService $branchDeliveryService): RedirectResponse
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:individual,company',
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'company_name' => 'nullable|string|max:180',
            'email' => 'required|email|max:190',
            'phone' => 'required|string|max:40',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:120',
            'postal_code' => 'required|string|max:40',
            'country' => 'required|string|max:120',
            'different_delivery_address' => 'sometimes|boolean',
            'order_type' => 'required|in:delivery,pick_up',
            'order_notes' => 'nullable|string',
            'payment_method' => 'required|string',
            'cart_data' => 'required|json',
            'gateway_payment_intent_id' => 'nullable|string|max:255',
        ]);

        if (($validated['customer_type'] ?? 'individual') !== 'company') {
            $validated['company_name'] = null;
        }

        $paymentMethod = $this->findActivePaymentMethod($validated['payment_method']);
        if (! $paymentMethod) {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_unavailable_payment_method'),
            ]);
        }

        $cartItems = json_decode($validated['cart_data'], true);
        if (empty($cartItems) || ! is_array($cartItems)) {
            return back()->withErrors(['cart_data' => __('frontend_basket_empty')])->withInput();
        }

        $customer = session()->has('frontend_customer_id') ? Customer::find(session('frontend_customer_id')) : null;
        $orderPricingService = app(OrderPricingService::class);
        $totals = $orderPricingService->calculate($cartItems, $validated['order_type'], $customer);
        
        $subtotal = $totals['subtotal'];
        $shippingCosts = $totals['shipping_costs'];
        $vatAmount = $totals['vat_amount'];
        $grandTotal = $totals['grand_total'];
        $deliverySummary = $totals['delivery_summary'];

        $currentCurrency = $this->resolveCurrentCurrency();

        $shippingFee = $deliverySummary['shipping_fee'] ?? null;
        $minOrderAmount = (float) ($shippingFee['min_order_amount'] ?? 0);
        if ($minOrderAmount > 0 && $grandTotal < $minOrderAmount) {
            $currencyCode = strtoupper($currentCurrency?->code ?: 'USD');
            $localizedAmount = $currencyCode . ' ' . number_format($minOrderAmount, 2);
            return back()
                ->withErrors(['cart_data' => __('shipping_min_order_amount_error', ['amount' => $localizedAmount])])
                ->withInput();
        }

        $paymentStatus = in_array($paymentMethod['type'], ['cash_on_delivery', 'bank_account'], true) ? 'pending' : 'paid';
        $paymentReference = null;
        $paymentPayload = null;

        if ($this->requiresGatewayCardForm($paymentMethod)) {
            $gatewayPaymentIntentId = $validated['gateway_payment_intent_id']
                ?: (
                    session('frontend_pending_payment_method_code') === $paymentMethod['code']
                        ? session('frontend_pending_payment_intent_id')
                        : null
                );

            Log::info('Checkout gateway verification input', [
                'payment_method_code' => $paymentMethod['code'],
                'posted_payment_intent_id' => $validated['gateway_payment_intent_id'] ?? null,
                'session_payment_intent_id' => session('frontend_pending_payment_intent_id'),
                'session_payment_method_code' => session('frontend_pending_payment_method_code'),
                'resolved_payment_intent_id' => $gatewayPaymentIntentId,
            ]);

            if (empty($gatewayPaymentIntentId)) {
                throw ValidationException::withMessages([
                    'payment_method' => __('frontend_payment_confirmation_missing'),
                ]);
            }

            $paymentVerification = $this->verifyGatewayPayment(
                $paymentMethod,
                $gatewayPaymentIntentId,
                $grandTotal,
                $currentCurrency
            );

            $paymentStatus = $paymentVerification['status'];
            $paymentReference = $paymentVerification['reference'];
            $paymentPayload = $paymentVerification['payload'];
        } elseif ($paymentMethod['type'] === 'bank_account') {
            $paymentPayload = [
                'account_title' => $paymentMethod['config']['account_title'] ?? null,
                'iban' => $paymentMethod['config']['iban'] ?? null,
                'branch_name' => $paymentMethod['config']['branch_name'] ?? null,
                'account_number' => $paymentMethod['config']['account_number'] ?? null,
            ];
        }

        return DB::transaction(function () use (
            $validated,
            $subtotal,
            $shippingCosts,
            $vatAmount,
            $grandTotal,
            $deliverySummary,
            $cartItems,
            $paymentMethod,
            $paymentStatus,
            $paymentReference,
            $paymentPayload,
            $currentCurrency
        ) {
            $order = Order::create([
                'customer_id' => session('frontend_customer_id'),
                'organization_id' => $deliverySummary['branch']['id'] ?? null,
                'shipping_fee_id' => $deliverySummary['shipping_fee']['id'] ?? null,
                'delivery_distance_km' => $deliverySummary['distance_km'] ?? null,
                'customer_latitude' => $deliverySummary['customer_latitude'] ?? null,
                'customer_longitude' => $deliverySummary['customer_longitude'] ?? null,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'company_name' => $validated['company_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'country' => $validated['country'],
                'different_delivery_address' => $validated['different_delivery_address'] ?? false,
                'order_type' => $validated['order_type'],
                'order_notes' => $validated['order_notes'],
                'payment_method' => $paymentMethod['code'],
                'payment_gateway' => $this->paymentGatewayLabel($paymentMethod),
                'payment_status' => $paymentStatus,
                'payment_reference' => $paymentReference,
                'payment_currency' => strtoupper($currentCurrency?->code ?: 'USD'),
                'payment_payload' => $paymentPayload,
                'subtotal' => $subtotal,
                'shipping_costs' => $shippingCosts,
                'vat_amount' => $vatAmount,
                'grand_total' => $grandTotal,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'serial_number' => $item['serial_number'] ?? null,
                    'title' => $item['title'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_total'],
                    'line_total' => $item['line_total'],
                    'addons' => $item['addons'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            session()->forget([
                'frontend_pending_payment_intent_id',
                'frontend_pending_payment_method_code',
            ]);

            return redirect()->route('frontend.checkout.success')
                ->with('frontend_checkout_success', true)
                ->with('order_id', $order->id);
        });
    }

    public function success(): View|RedirectResponse
    {
        if (! session('frontend_checkout_success')) {
            return redirect()->route('frontend.home');
        }

        return view('frontend.checkout-success', [
            'hasFrontendCustomer' => session()->has('frontend_customer_id'),
        ]);
    }

    private function checkoutPaymentMethods(): array
    {
        return collect(Setting::paymentMethods(true))
            ->filter(fn ($method) => is_array($method) && ! empty($method['code']))
            ->map(function (array $method) {
                $type = $method['type'] ?? 'custom';
                $config = is_array($method['config'] ?? null) ? $method['config'] : [];
                $credentials = $this->resolveGatewayCredentials($method);

                return [
                    'code' => $method['code'],
                    'title' => $method['title'] ?? Str::headline(str_replace('_', ' ', $method['code'])),
                    'description' => $method['description'] ?? '',
                    'type' => $type,
                    'requires_card_form' => $this->requiresGatewayCardForm($method),
                    'public_key' => $credentials['public_key'],
                    'bank_details' => $type === 'bank_account' ? [
                        'account_title' => $config['account_title'] ?? '',
                        'iban' => $config['iban'] ?? '',
                        'branch_name' => $config['branch_name'] ?? '',
                        'account_number' => $config['account_number'] ?? '',
                    ] : null,
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
                'environment' => $environment,
                'public_key' => $environment === 'live'
                    ? trim((string) ($config['live_public_key'] ?? ''))
                    : trim((string) ($config['sandbox_public_key'] ?? '')),
                'secret_key' => $environment === 'live'
                    ? trim((string) ($config['live_secret_key'] ?? ''))
                    : trim((string) ($config['sandbox_secret_key'] ?? '')),
            ];
        }

        if (($method['type'] ?? null) === 'custom') {
            return [
                'environment' => $environment,
                'public_key' => $environment === 'live'
                    ? trim((string) ($config['live_public_key'] ?? ''))
                    : trim((string) ($config['sandbox_public_key'] ?? '')),
                'secret_key' => $environment === 'live'
                    ? trim((string) ($config['live_private_key'] ?? ''))
                    : trim((string) ($config['sandbox_private_key'] ?? '')),
            ];
        }

        return [
            'environment' => $environment,
            'public_key' => null,
            'secret_key' => null,
        ];
    }

    private function requiresGatewayCardForm(array $method): bool
    {
        return ! in_array($method['type'] ?? null, ['cash_on_delivery', 'bank_account'], true);
    }

    // Removed calculateTotals as it is now handled by OrderPricingService

    private function resolveCurrentCurrency(): ?Currency
    {
        return Currency::find(session('frontend_currency_id')) ?: Currency::active()->first() ?: Currency::query()->first();
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

    private function verifyGatewayPayment(array $paymentMethod, string $paymentIntentId, float $expectedTotal, ?Currency $currency): array
    {
        $credentials = $this->resolveGatewayCredentials($paymentMethod);
        if (! $credentials['secret_key']) {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_payment_method_missing_keys'),
            ]);
        }

        $response = Http::withBasicAuth($credentials['secret_key'], '')
            ->get('https://api.stripe.com/v1/payment_intents/' . $paymentIntentId);

        $payload = $response->json();
        if (! $response->successful() || empty($payload['id'])) {
            throw ValidationException::withMessages([
                'payment_method' => $payload['error']['message'] ?? __('frontend_payment_verification_failed'),
            ]);
        }

        $expectedMinorAmount = $this->toMinorAmount($expectedTotal, $currency);
        $expectedCurrency = strtolower($currency?->code ?: 'usd');

        if (($payload['status'] ?? null) !== 'succeeded') {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_payment_not_completed'),
            ]);
        }

        if ((int) ($payload['amount'] ?? 0) !== $expectedMinorAmount || strtolower((string) ($payload['currency'] ?? '')) !== $expectedCurrency) {
            throw ValidationException::withMessages([
                'payment_method' => __('frontend_payment_amount_mismatch'),
            ]);
        }

        return [
            'status' => 'paid',
            'reference' => $payload['id'],
            'payload' => [
                'gateway_type' => $paymentMethod['type'] ?? 'stripe',
                'status' => $payload['status'] ?? null,
                'currency' => $payload['currency'] ?? null,
                'amount' => $payload['amount'] ?? null,
                'environment' => $credentials['environment'] ?? 'sandbox',
            ],
        ];
    }

    private function paymentGatewayLabel(array $paymentMethod): string
    {
        return match ($paymentMethod['type'] ?? 'custom') {
            'cash_on_delivery' => 'offline',
            'bank_account' => 'offline',
            default => 'stripe',
        };
    }
}
