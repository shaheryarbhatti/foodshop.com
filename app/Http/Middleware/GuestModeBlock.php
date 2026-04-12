<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestModeBlock
{
    public function handle(Request $request, Closure $next): Response
    {
        $guestMode = strtolower((string) env('GUEST_MODE', 'off')) === 'on';

        if (! $guestMode) {
            return $next($request);
        }

        $method = strtoupper($request->method());
        $isWrite = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        if (! $isWrite) {
            return $next($request);
        }

        if ($request->routeIs(
                'login',
                'logout',
                'userlogout',
                'password.*',
                'admin.login',
                'admin.login.submit',
                'frontend.login.submit',
                'frontend.register.submit'
            )
            || $request->is('login')
            || $request->is('logout')
            || $request->is('admin-login')
            || $request->is('register')) {
            return $next($request);
        }

        $message = $this->resolveGuestModeMessage($request, $method);

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back()->with('guest_mode_block', $message);
    }

    private function resolveGuestModeMessage(Request $request, string $method): string
    {
        if ($request->routeIs(
            'frontend.checkout.submit',
            'frontend.checkout.payment-intent',
            'frontend.orders.process-balance',
            'frontend.orders.balance-intent'
        )) {
            return 'You cannot place an order in guest mode.';
        }

        return match ($method) {
            'DELETE' => 'You cannot delete anything in guest mode.',
            'PUT', 'PATCH' => 'You cannot update anything in guest mode.',
            default => 'You cannot save anything in guest mode.',
        };
    }
}
