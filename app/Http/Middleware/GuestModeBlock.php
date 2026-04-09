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

        if ($request->routeIs('login', 'logout', 'userlogout', 'password.*')
            || $request->is('login')
            || $request->is('logout')) {
            return $next($request);
        }

        $message = match ($method) {
            'DELETE' => 'You cannot delete anything in guest mode.',
            'PUT', 'PATCH' => 'You cannot update anything in guest mode.',
            default => 'You cannot save anything in guest mode.',
        };

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back()->with('guest_mode_block', $message);
    }
}
