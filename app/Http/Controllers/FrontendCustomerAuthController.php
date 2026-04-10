<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\BranchDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FrontendCustomerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->hasAnyRole(['Staff', 'Driver'])
                ? redirect()->route('frontend.staff.dashboard')
                : redirect()->route('home');
        }

        if (session()->has('frontend_customer_id')) {
            return redirect()->route('frontend.dashboard');
        }

        return view('frontend.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login_type' => ['required', Rule::in(['customer', 'staff', 'driver'])],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validated['login_type'] === 'customer') {
            return $this->loginCustomer($validated, $request);
        }

        return $this->loginStaff($validated, $request);
    }

    private function loginCustomer(array $validated, Request $request): RedirectResponse
    {
        $customer = Customer::where('email', $validated['email'])->where('status', true)->first();
        if (! $customer || ! Hash::check($validated['password'], $customer->password)) {
            return back()->withErrors(['email' => __('frontend_invalid_customer_credentials')])->withInput();
        }

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->regenerate();
        $request->session()->put([
            'frontend_customer_id' => $customer->id,
            'frontend_customer_name' => $customer->full_name,
        ]);

        return redirect()->route('frontend.dashboard')->with('status', __('frontend_login_success'));
    }

    private function loginStaff(array $validated, Request $request): RedirectResponse
    {
        $requiredRole = $validated['login_type'] === 'staff' ? 'Staff' : 'Driver';

        $user = User::query()
            ->where('email', $validated['email'])
            ->where('status', true)
            ->whereHas('roles', fn ($query) => $query->whereRaw('LOWER(name) = ?', [strtolower($requiredRole)]))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors([
                    'email' => $validated['login_type'] === 'driver'
                        ? 'Invalid driver credentials.'
                        : 'Invalid staff credentials.',
                ])
                ->withInput();
        }

        $request->session()->forget([
            'frontend_customer_id',
            'frontend_customer_name',
            'frontend_checkout_success',
            'order_id',
        ]);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('frontend.staff.dashboard'));
    }

    public function showRegister()
    {
        if (Auth::check() && Auth::user()->hasAnyRole(['Staff', 'Driver'])) {
            return redirect()->route('frontend.staff.dashboard');
        }

        if (session()->has('frontend_customer_id')) {
            return redirect()->route('frontend.dashboard');
        }

        return view('frontend.register', [
            'countries' => Country::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $this->validateCustomerPayload($request, true);

        $customer = Customer::create($this->prepareCustomerData($validated));

        session([
            'frontend_customer_id' => $customer->id,
            'frontend_customer_name' => $customer->full_name,
        ]);

        return redirect()->route('frontend.dashboard')->with('status', __('frontend_register_success'));
    }

    public function dashboard(): View|RedirectResponse
    {
        $customer = $this->currentCustomer();
        if (! $customer) {
            return redirect()->route('login');
        }

        $orders = Order::query()
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->with('items')
            ->get();

        $statusGroups = [
            'pending' => ['pending_payment'],
            'active' => ['processing', 'shipped'],
            'completed' => ['delivered'],
            'issues' => ['cancelled', 'returned', 'failed'],
        ];

        return view('frontend.dashboard', [
            'customer' => $customer,
            'recentOrders' => $orders->take(8),
            'stats' => [
                'total_orders' => $orders->count(),
                'pending_orders' => $orders->whereIn('order_status', $statusGroups['pending'])->count(),
                'active_orders' => $orders->whereIn('order_status', $statusGroups['active'])->count(),
                'completed_orders' => $orders->whereIn('order_status', $statusGroups['completed'])->count(),
                'total_spent' => round((float) $orders->sum('grand_total'), 2),
            ],
        ]);
    }

    public function profile(): View|RedirectResponse
    {
        $customer = $this->currentCustomer();
        if (! $customer) {
            return redirect()->route('login');
        }

        return view('frontend.profile', [
            'customer' => $customer,
            'countries' => Country::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = $this->currentCustomer();
        if (! $customer) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'customer_type' => ['required', Rule::in(['individual', 'company'])],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:190', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:40'],
            'country_id' => ['required', 'exists:countries,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
        ]);

        if (($validated['customer_type'] ?? 'individual') !== 'company') {
            $validated['company_name'] = null;
        }

        $validated['country'] = Country::find($validated['country_id'])?->name ?: $customer->country;

        if ($request->hasFile('image')) {
            if ($customer->image && Storage::disk('public')->exists($customer->image)) {
                Storage::disk('public')->delete($customer->image);
            }
            $validated['image'] = $request->file('image')->store('customers', 'public');
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $customer->update($validated);

        session([
            'frontend_customer_name' => $customer->fresh()->full_name,
        ]);

        return redirect()->route('frontend.profile')->with('status', 'Profile updated successfully.');
    }

    public function logout(): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'You have been logged out successfully.');
        }

        session()->forget([
            'frontend_customer_id',
            'frontend_customer_name',
            'frontend_checkout_success',
            'order_id',
        ]);

        return redirect()->route('frontend.home');
    }

    public function orderDetails(Order $order): View|RedirectResponse
    {
        $customer = $this->currentCustomer();
        if (! $customer) {
            return redirect()->route('login');
        }

        if ((int) $order->customer_id !== (int) $customer->id) {
            return redirect()->route('frontend.dashboard');
        }

        $order->loadMissing(['items', 'organization.location']);

        return view('frontend.dashboard-order', [
            'customer' => $customer,
            'order' => $order,
        ]);
    }

    public function completeAddress(Request $request, BranchDeliveryService $branchDeliveryService)
    {
        if (session()->has('frontend_customer_id')) {
            $customer = Customer::find(session('frontend_customer_id'));
            if ($customer) {
                return response()->json([
                    'message' => __('frontend_address_already_completed'),
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->full_name,
                        'email' => $customer->email,
                        'has_address' => filled($customer->address)
                            && filled($customer->city)
                            && filled($customer->postal_code)
                            && filled($customer->country_id),
                    ],
                    'delivery_summary' => $branchDeliveryService->summarizeForCustomer($customer, (float) $request->input('subtotal', 0), (string) $request->input('order_type', 'delivery')),
                ]);
            }
        }

        $validated = $this->validateCustomerPayload($request, false);
        $customer = Customer::create($this->prepareCustomerData($validated));

        session([
            'frontend_customer_id' => $customer->id,
            'frontend_customer_name' => $customer->full_name,
        ]);

        return response()->json([
            'message' => __('frontend_address_completed_successfully'),
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->email,
                'has_address' => true,
            ],
            'delivery_summary' => $branchDeliveryService->summarizeForCustomer($customer, (float) $request->input('subtotal', 0), (string) $request->input('order_type', 'delivery')),
        ]);
    }

    public function addressSearch(Request $request)
    {
        if (Setting::get('map_provider', 'leaflet') !== 'leaflet') {
            throw ValidationException::withMessages([
                'provider' => __('frontend_address_search_provider_mismatch'),
            ]);
        }

        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) < 3) {
            return response()->json(['results' => []]);
        }

        $response = Http::timeout(12)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => config('app.name', 'Foodshop') . '/1.0',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 5,
                'q' => $query,
            ]);

        if (! $response->ok()) {
            return response()->json(['results' => []]);
        }

        $results = collect($response->json() ?: [])
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => $this->formatAddressResult($item))
            ->filter(fn ($item) => filled($item['label']))
            ->values()
            ->all();

        return response()->json(['results' => $results]);
    }

    public function geocodeAddress(Request $request, BranchDeliveryService $branchDeliveryService)
    {
        if (Setting::get('map_provider', 'leaflet') !== 'leaflet') {
            throw ValidationException::withMessages([
                'provider' => __('frontend_address_search_provider_mismatch'),
            ]);
        }

        $result = $branchDeliveryService->geocodeAddress([
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'postal_code' => $request->input('postal_code'),
            'country' => $request->input('country'),
        ]);

        return response()->json(['result' => $result]);
    }

    public function reverseGeocodeAddress(Request $request, BranchDeliveryService $branchDeliveryService)
    {
        if (Setting::get('map_provider', 'leaflet') !== 'leaflet') {
            throw ValidationException::withMessages([
                'provider' => __('frontend_address_search_provider_mismatch'),
            ]);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $branchDeliveryService->reverseGeocodeCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return response()->json(['result' => $result]);
    }

    public function checkEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        return response()->json([
            'exists' => Customer::where('email', $validated['email'])->exists(),
        ]);
    }

    private function validateCustomerPayload(Request $request, bool $requirePasswordConfirmation): array
    {
        $rules = [
            'customer_type' => ['required', Rule::in(['individual', 'company'])],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:190', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:120'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'password' => $requirePasswordConfirmation
                ? ['required', 'string', 'min:6', 'max:255', 'confirmed']
                : ['required', 'string', 'min:6', 'max:255'],
        ];

        $validated = $request->validate($rules);

        if (($validated['customer_type'] ?? 'individual') !== 'company') {
            $validated['company_name'] = null;
        }

        if (! empty($validated['country_id'])) {
            $validated['country'] = Country::find($validated['country_id'])?->name ?: $validated['country'];
        }

        return $validated;
    }

    private function prepareCustomerData(array $validated): array
    {
        $validated['status'] = true;
        $validated['password'] = Hash::make($validated['password']);

        return $validated;
    }

    private function currentCustomer(): ?Customer
    {
        if (! session()->has('frontend_customer_id')) {
            return null;
        }

        return Customer::find(session('frontend_customer_id'));
    }

    private function formatAddressResult(array $item): array
    {
        $addressParts = is_array($item['address'] ?? null) ? $item['address'] : [];

        return [
            'title' => (string) ($item['name'] ?? strtok((string) ($item['display_name'] ?? ''), ',')),
            'label' => (string) ($item['display_name'] ?? ''),
            'address' => (string) ($item['display_name'] ?? ''),
            'latitude' => (string) ($item['lat'] ?? ''),
            'longitude' => (string) ($item['lon'] ?? ''),
            'city' => (string) ($addressParts['city'] ?? $addressParts['town'] ?? $addressParts['village'] ?? $addressParts['municipality'] ?? ''),
            'postal_code' => (string) ($addressParts['postcode'] ?? ''),
            'country' => (string) ($addressParts['country'] ?? ''),
            'country_code' => strtoupper((string) ($addressParts['country_code'] ?? '')),
        ];
    }
}
