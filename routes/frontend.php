<?php

use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ShippingFee;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\Allergy;
use App\Http\Controllers\FrontendCustomerAuthController;
use App\Http\Controllers\FrontendCheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
|
| Define public/frontend panel routes for the tickets project here.
| Backend/admin routes should continue to live in routes/web.php.
|
*/

Route::get('/login', [FrontendCustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [FrontendCustomerAuthController::class, 'login'])->name('frontend.login.submit');
Route::post('/logout', [FrontendCustomerAuthController::class, 'logout'])->name('frontend.logout');
Route::get('/register', [FrontendCustomerAuthController::class, 'showRegister'])->name('frontend.register');
Route::post('/register', [FrontendCustomerAuthController::class, 'register'])->name('frontend.register.submit');
Route::get('/dashboard', [FrontendCustomerAuthController::class, 'dashboard'])->name('frontend.dashboard');
Route::get('/profile', [FrontendCustomerAuthController::class, 'profile'])->name('frontend.profile');
Route::post('/profile', [FrontendCustomerAuthController::class, 'updateProfile'])->name('frontend.profile.update');
Route::get('/dashboard/orders/{order}', [FrontendCustomerAuthController::class, 'orderDetails'])->name('frontend.dashboard.order');
Route::post('/complete-address', [FrontendCustomerAuthController::class, 'completeAddress'])->name('frontend.complete-address');
Route::post('/check-customer-email', [FrontendCustomerAuthController::class, 'checkEmail'])->name('frontend.check-customer-email');
Route::get('/address-search', [FrontendCustomerAuthController::class, 'addressSearch'])->name('frontend.address-search');
Route::post('/geocode-address', [FrontendCustomerAuthController::class, 'geocodeAddress'])->name('frontend.geocode-address');
Route::post('/reverse-geocode-address', [FrontendCustomerAuthController::class, 'reverseGeocodeAddress'])->name('frontend.reverse-geocode-address');

Route::get('/currency/{currency}', function (Currency $currency) {
    session(['frontend_currency_id' => $currency->id]);

    return redirect()->back();
})->name('frontend.currency.switch');

Route::get('/', function () {
    $currentCurrency = Currency::find(session('frontend_currency_id')) ?: Currency::active()->first() ?: Currency::query()->first();
    $frontendCustomer = session()->has('frontend_customer_id')
        ? Customer::find(session('frontend_customer_id'))
        : null;

    return view('frontend.home', [
        'categories' => Category::with(['products' => function ($query) {
            $query->where('status', true)
                ->with(['addons' => function ($addonQuery) {
                    $addonQuery->where('status', true)
                        ->with(['values' => function ($valueQuery) {
                            $valueQuery->where('status', true)->orderBy('title');
                        }]);
                }])
                ->orderBy('serial_number');
        }])->where('status', true)->orderBy('title')->get(),
        'featuredProduct' => Product::where('status', true)->orderBy('serial_number')->first(),
        'activeTax' => Tax::where('status', true)->orderBy('id')->first(),
        'currentCurrency' => $currentCurrency,
        'frontendCustomer' => $frontendCustomer,
        'countries' => Country::where('status', true)->orderBy('name')->get(),
        'allAllergies' => Allergy::where('status', true)->orderBy('code')->get(),
        'mapProvider' => Setting::get('map_provider', 'leaflet'),
        'googleMapsApiKey' => Setting::get('google_maps_api_key', ''),
    ]);
})->name('frontend.home');

Route::get('/cart', function () {
    $currentCurrency = Currency::find(session('frontend_currency_id')) ?: Currency::active()->first() ?: Currency::query()->first();

    return view('frontend.cart', [
        'activeTax' => Tax::where('status', true)->orderBy('id')->first(),
        'activeShippingFee' => ShippingFee::where('status', true)->orderBy('id')->first(),
        'currentCurrency' => $currentCurrency,
    ]);
})->name('frontend.cart');

Route::get('/checkout', [FrontendCheckoutController::class, 'index'])->name('frontend.checkout');
Route::post('/checkout/delivery-summary', [FrontendCheckoutController::class, 'deliverySummary'])->name('frontend.checkout.delivery-summary');
Route::post('/checkout/payment-intent', [FrontendCheckoutController::class, 'createPaymentIntent'])->name('frontend.checkout.payment-intent');
Route::post('/checkout', [FrontendCheckoutController::class, 'submit'])->name('frontend.checkout.submit');
Route::get('/checkout/success', [FrontendCheckoutController::class, 'success'])->name('frontend.checkout.success');

Route::get('/knowledge-base', function () {
    return redirect()->route('frontend.home');
})->name('frontend.knowledge-base');

Route::get('/my-tickets', function () {
    return redirect()->route('login');
})->name('frontend.my-tickets');
