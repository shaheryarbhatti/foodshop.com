<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SidebarManagementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductAddonController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ShippingFeeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\AllergyController;

Route::get('/license/invalid', function () {
    return view('license.invalid');
})->name('license.invalid');

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    return "Cleared!";
});

Route::middleware(['auth'])->post('/license/update', [SettingController::class, 'updateLicense'])->name('license.update');

Route::middleware('guest')->group(function () {
    Route::get('/admin-login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin-login', [LoginController::class, 'login'])->name('admin.login.submit');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    Route::get('/password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('/password/confirm', [ConfirmPasswordController::class, 'confirm']);

    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/language/{locale}', function ($locale) {
    $allowed = ['en', 'id'];

    if (in_array($locale, $allowed)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('language.switch');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/userlogout', [App\Http\Controllers\HomeController::class, 'logout'])->name('userlogout');
Route::middleware(['auth'])->get('/documentation', function () {
    return view('documentation.index');
})->name('documentation');

// Permission Management Routes
Route::middleware(['auth'])->prefix('permissions')->group(function () {

    Route::get('/', [PermissionController::class, 'index'])->name('permissions.manage');
    Route::get('/create', [PermissionController::class, 'create'])->name('permissions.add');
    Route::post('/', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

});

// Role Management Routes
Route::middleware(['auth'])->prefix('roles')->group(function () {

    Route::get('/', [RoleController::class, 'index'])->name('roles.manage');
    Route::get('/create', [RoleController::class, 'create'])->name('roles.add');
    Route::post('/', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

});

// User Management Routes
Route::middleware(['auth'])->prefix('users')->group(function () {

    Route::get('/', [UserController::class, 'index'])->name('users.manage');
    Route::get('/create', [UserController::class, 'create'])->name('users.add');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');

});

// Currency Management Routes
Route::middleware(['auth'])->prefix('currencies')->group(function () {

    Route::get('/', [CurrencyController::class, 'index'])->name('currencies.manage');
    Route::get('/create', [CurrencyController::class, 'create'])->name('currencies.add');
    Route::post('/', [CurrencyController::class, 'store'])->name('currencies.store');
    Route::post('/base', [CurrencyController::class, 'setBase'])->name('currencies.setBase');
    Route::get('/{currency}', [CurrencyController::class, 'show'])->name('currencies.show');
    Route::get('/{currency}/edit', [CurrencyController::class, 'edit'])->name('currencies.edit');
    Route::put('/{currency}', [CurrencyController::class, 'update'])->name('currencies.update');
    Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');

});

Route::middleware(['auth'])->prefix('organizations')->group(function () {

    Route::get('/', [OrganizationController::class, 'index'])->name('organizations.manage');
    Route::get('/create', [OrganizationController::class, 'create'])->name('organizations.add');
    Route::get('/address-search', [OrganizationController::class, 'addressSearch'])->name('organizations.address-search');
    Route::get('/geocode-address', [OrganizationController::class, 'geocodeAddress'])->name('organizations.geocode-address');
    Route::post('/reverse-geocode-address', [OrganizationController::class, 'reverseGeocodeAddress'])->name('organizations.reverse-geocode-address');
    Route::post('/', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

});

Route::middleware(['auth'])->prefix('countries')->group(function () {

    Route::get('/', [CountryController::class, 'index'])->name('countries.manage');
    Route::get('/create', [CountryController::class, 'create'])->name('countries.add');
    Route::post('/', [CountryController::class, 'store'])->name('countries.store');
    Route::get('/{country}/edit', [CountryController::class, 'edit'])->name('countries.edit');
    Route::put('/{country}', [CountryController::class, 'update'])->name('countries.update');
    Route::delete('/{country}', [CountryController::class, 'destroy'])->name('countries.destroy');

});

Route::middleware(['auth'])->prefix('regions')->group(function () {

    Route::get('/', [RegionController::class, 'index'])->name('regions.manage');
    Route::get('/create', [RegionController::class, 'create'])->name('regions.add');
    Route::post('/', [RegionController::class, 'store'])->name('regions.store');
    Route::get('/{region}/edit', [RegionController::class, 'edit'])->name('regions.edit');
    Route::put('/{region}', [RegionController::class, 'update'])->name('regions.update');
    Route::delete('/{region}', [RegionController::class, 'destroy'])->name('regions.destroy');

});

Route::middleware(['auth'])->prefix('locations')->group(function () {

    Route::get('/', [LocationController::class, 'index'])->name('locations.manage');
    Route::get('/create', [LocationController::class, 'create'])->name('locations.add');
    Route::post('/', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

});






Route::middleware(['auth'])->prefix('allergies')->group(function () {
    Route::get('/', [AllergyController::class, 'index'])->name('allergies.manage');
    Route::get('/create', [AllergyController::class, 'create'])->name('allergies.add');
    Route::post('/', [AllergyController::class, 'store'])->name('allergies.store');
    Route::delete('/delete-all', [AllergyController::class, 'bulkDestroy'])->name('allergies.destroyAll');
    Route::get('/{allergy}/edit', [AllergyController::class, 'edit'])->name('allergies.edit');
    Route::put('/{allergy}', [AllergyController::class, 'update'])->name('allergies.update');
    Route::delete('/{allergy}', [AllergyController::class, 'destroy'])->name('allergies.destroy');
});

Route::middleware(['auth'])->prefix('categories')->group(function () {

    Route::get('/', [CategoryController::class, 'index'])->name('categories.manage');
    Route::get('/create', [CategoryController::class, 'create'])->name('categories.add');
    Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

});

Route::middleware(['auth'])->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.manage');
    Route::get('/create', [ProductController::class, 'create'])->name('products.add');
    Route::post('/', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/delete-all', [ProductController::class, 'destroyAll'])->name('products.destroyAll');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

Route::middleware(['auth'])->prefix('addons')->group(function () {
    Route::get('/', [ProductAddonController::class, 'index'])->name('addons.manage');
    Route::get('/create', [ProductAddonController::class, 'create'])->name('addons.add');
    Route::post('/', [ProductAddonController::class, 'store'])->name('addons.store');
    Route::delete('/delete-all', [ProductAddonController::class, 'destroyAll'])->name('addons.destroyAll');
    Route::get('/{addon}/edit', [ProductAddonController::class, 'edit'])->name('addons.edit');
    Route::put('/{addon}', [ProductAddonController::class, 'update'])->name('addons.update');
    Route::delete('/{addon}', [ProductAddonController::class, 'destroy'])->name('addons.destroy');
});

Route::middleware(['auth'])->prefix('taxes')->group(function () {
    Route::get('/', [TaxController::class, 'index'])->name('taxes.manage');
    Route::get('/create', [TaxController::class, 'create'])->name('taxes.add');
    Route::post('/', [TaxController::class, 'store'])->name('taxes.store');
    Route::delete('/delete-all', [TaxController::class, 'destroyAll'])->name('taxes.destroyAll');
    Route::get('/{tax}/edit', [TaxController::class, 'edit'])->name('taxes.edit');
    Route::put('/{tax}', [TaxController::class, 'update'])->name('taxes.update');
    Route::delete('/{tax}', [TaxController::class, 'destroy'])->name('taxes.destroy');
});

Route::middleware(['auth'])->prefix('shipping-fees')->group(function () {
    Route::get('/', [ShippingFeeController::class, 'index'])->name('shipping.manage');
    Route::get('/create', [ShippingFeeController::class, 'create'])->name('shipping.add');
    Route::post('/', [ShippingFeeController::class, 'store'])->name('shipping.store');
    Route::delete('/delete-all', [ShippingFeeController::class, 'destroyAll'])->name('shipping.destroyAll');
    Route::get('/{shipping}/edit', [ShippingFeeController::class, 'edit'])->name('shipping.edit');
    Route::put('/{shipping}', [ShippingFeeController::class, 'update'])->name('shipping.update');
    Route::delete('/{shipping}', [ShippingFeeController::class, 'destroy'])->name('shipping.destroy');
});

Route::middleware(['auth'])->prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('customers.manage');
    Route::get('/create', [CustomerController::class, 'create'])->name('customers.add');
    Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

Route::middleware(['auth'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('orders.manage');
    Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::delete('/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/{order}/invoice/download', [OrderController::class, 'downloadInvoice'])->name('orders.invoice.download');
});
Route::middleware(['auth'])->prefix('sidebar-management')->group(function () {

    Route::get('/', [SidebarManagementController::class, 'index'])->name('sidebar.manage');
    Route::get('/create', [SidebarManagementController::class, 'create'])->name('sidebar.module.create');
    Route::post('/module', [SidebarManagementController::class, 'storeModule'])->name('sidebar.module.store');
    Route::post('/option', [SidebarManagementController::class, 'storeOption'])->name('sidebar.option.store');
    Route::get('/module/{module}/edit', [SidebarManagementController::class, 'editModule'])->name('sidebar.module.edit');
    Route::put('/module/{module}', [SidebarManagementController::class, 'updateModule'])->name('sidebar.module.update');
    Route::delete('/module/{module}', [SidebarManagementController::class, 'destroyModule'])->name('sidebar.module.destroy');
    Route::delete('/option/{option}', [SidebarManagementController::class, 'destroyOption'])->name('sidebar.option.destroy');

});

// Setting Management Routes
Route::middleware(['auth'])->prefix('settings')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/update', [SettingController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth'])->prefix('email-template')->group(function () {
    Route::get('/', [EmailTemplateController::class, 'manage'])->name('email.manage');
    Route::post('/', [EmailTemplateController::class, 'update'])->name('email.update');
    Route::post('/test', [EmailTemplateController::class, 'sendTest'])->name('email.test');
});

Route::get('/orders/{order}/pay-balance', [\App\Http\Controllers\FrontendOrderPaymentController::class, 'payBalance'])->name('frontend.orders.pay-balance');
Route::post('/orders/{order}/pay-balance', [\App\Http\Controllers\FrontendOrderPaymentController::class, 'processBalancePayment'])->name('frontend.orders.process-balance');
Route::post('/orders/{order}/pay-balance/intent', [\App\Http\Controllers\FrontendOrderPaymentController::class, 'createBalancePaymentIntent'])->name('frontend.orders.balance-intent');
