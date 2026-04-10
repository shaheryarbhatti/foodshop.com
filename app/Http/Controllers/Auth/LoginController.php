<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function credentials(\Illuminate\Http\Request $request)
    {
        $credentials = [
            'email' => $request->input($this->username()),
            'password' => $request->input('password'),
        ];

        // Add the status condition
        $credentials['status'] = 1;

        return $credentials;
    }

    public function username()
    {
        return 'email';
    }

    protected function validateLogin(\Illuminate\Http\Request $request)
    {
        $request->validate([
            $this->username() => 'required|email',
            'password' => 'required|string',
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->hasAnyRole(['Staff', 'Driver'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Staff and drivers can only log in from the frontend portal.',
            ]);
        }

        $this->logPortalVisit($request, $user);
        return null;
    }

    private function logPortalVisit(Request $request, User $user): void
    {
        $todayKey = Carbon::today()->toDateString();
        if ($request->session()->get('portal_visit_logged') === $todayKey) {
            return;
        }

        $ip = $request->ip();
        $country = 'Unknown';
        $countryCode = null;

        if ($ip && ! in_array($ip, ['127.0.0.1', '::1'], true)) {
            try {
                $response = Http::timeout(3)->get('http://ip-api.com/json/' . $ip, [
                    'fields' => 'status,country,countryCode',
                ]);
                if ($response->ok() && $response->json('status') === 'success') {
                    $country = (string) $response->json('country');
                    $countryCode = (string) $response->json('countryCode');
                }
            } catch (\Throwable $e) {
                // Skip geo lookup failures
            }
        } elseif ($ip) {
            $country = 'Local';
            $countryCode = 'LOCAL';
        }

        SiteVisit::create([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'country' => $country ?: 'Unknown',
            'country_code' => $countryCode,
            'visited_at' => Carbon::now(),
        ]);

        $request->session()->put('portal_visit_logged', $todayKey);
    }

}
