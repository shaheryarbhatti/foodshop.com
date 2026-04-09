<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['en', 'id'];
        $locale = session('locale');
        if (! $locale) {
            $locale = 'en';
            session(['locale' => $locale]);
        }
        if (! in_array($locale, $allowed, true)) {
            $locale = 'en';
            session(['locale' => $locale]);
        }
        app()->setLocale($locale);
        \Illuminate\Support\Facades\Config::set('app.locale', $locale);

        $timezone = Setting::get('timezone', config('app.timezone', 'UTC'));
        if (! in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $timezone = config('app.timezone', 'UTC');
        }
        date_default_timezone_set($timezone);
        \Illuminate\Support\Facades\Config::set('app.timezone', $timezone);

        return $next($request);
    }
}
