<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AccessGate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('CHECK_ACCESS')) {
            return $next($request);
        }

        if ($request->routeIs('license.invalid') || $request->routeIs('license.update')) {
            return $next($request);
        }

        if (! auth()->check()) {
            return $next($request);
        }

        $serverUrl = trim((string) Setting::get('license_server_url'));
        $licenseKey = trim((string) Setting::get('license_key'));
        $projectKey = trim((string) Setting::get('license_project_key'));

        if ($serverUrl !== '' && ! preg_match('#^https?://#i', $serverUrl)) {
            $serverUrl = 'http://' . $serverUrl;
        }

        if ($serverUrl === '' || $licenseKey === '' || $projectKey === '') {
            return redirect()->route('license.invalid')->with('license_error', __('license_missing'));
        }

        $domain = strtolower((string) $request->getHost());
        $cacheKey = 'license_status_' . md5($serverUrl . '|' . $licenseKey . '|' . $projectKey . '|' . $domain);

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['valid'] ?? false)) {
            return $next($request);
        }

        $secret = env('LICENSE_PROJECT_SECRET');
        if (! $secret) {
            $secret = $this->readSecretFromFile();
        }
        if (! $secret) {
            $secret = config('app.key');
        }
        $payload = $licenseKey . '|' . $domain . '|' . $projectKey;
        $signature = hash_hmac('sha256', $payload, $secret);

        $verifyUrl = rtrim($serverUrl, '/') . '/api/license/verify';

        try {
            $response = Http::timeout(8)->acceptJson()->post($verifyUrl, [
                'project_key' => $projectKey,
                'license_key' => $licenseKey,
                'domain' => $domain,
                'signature' => $signature,
            ]);
        } catch (\Throwable $e) {
            $debug = env('LICENSE_DEBUG', false)
                ? ('URL: ' . $verifyUrl . ' | HTTP error: ' . $e->getMessage())
                : null;
            return redirect()->route('license.invalid')->with([
                'license_error' => __('license_server_unreachable'),
                'license_debug' => $debug,
            ]);
        }

        $debugPayload = null;
        if (env('LICENSE_DEBUG', false)) {
            $debugPayload = 'URL: ' . $verifyUrl . ' | Status: ' . $response->status() . ' Body: ' . $response->body();
        }

        if (! $response->ok()) {
            $message = $response->json('message') ?: __('license_invalid');
            return redirect()->route('license.invalid')->with([
                'license_error' => $message,
                'license_debug' => $debugPayload,
            ]);
        }

        $data = $response->json();
        if (! ($data['valid'] ?? false)) {
            return redirect()->route('license.invalid')->with([
                'license_error' => $data['message'] ?? __('license_invalid'),
                'license_debug' => $debugPayload,
            ]);
        }

        Cache::put($cacheKey, [
            'valid' => true,
            'checked_at' => now()->toDateTimeString(),
        ], now()->addHours(6));

        return $next($request);
    }

    private function readSecretFromFile(): ?string
    {
        try {
            if (! Storage::disk('local')->exists('dummyfile.txt')) {
                return null;
            }
            $value = trim((string) Storage::disk('local')->get('dummyfile.txt'));
            return $value !== '' ? $value : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
