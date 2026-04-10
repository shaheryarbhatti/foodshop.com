<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('system_settings') }}</title>

@extends('layouts.app')
@section('content')
@php
    $activeCurrency = \App\Services\CurrencyService::getActiveCurrency();
    $currencyLabel = $activeCurrency ? trim($activeCurrency->symbol . ' ' . $activeCurrency->code) : 'Rp';
    $currencyCode = $activeCurrency ? $activeCurrency->code : 'IDR';
    $currencyRate = $activeCurrency ? (float) $activeCurrency->exchange_rate : 1;
    $baseCurrencyCode = \App\Models\Setting::get('base_currency_code', 'IDR');
    $baseCurrency = \App\Models\Currency::where('code', $baseCurrencyCode)->first();
    $baseLabel = $baseCurrency ? trim($baseCurrency->symbol . ' ' . $baseCurrency->code) : $baseCurrencyCode;
    $themePrimary = \App\Models\Setting::get('theme_primary', '#7367f0');
    $themeSecondary = \App\Models\Setting::get('theme_secondary', '#00cfe8');
    $themeAccent = \App\Models\Setting::get('theme_accent', '#0f9b8e');
    $sidebarDashboardTextColor = \App\Models\Setting::get('sidebar_dashboard_text_color', '#ffffff');
    $timezoneValue = \App\Models\Setting::get('timezone', config('app.timezone', 'UTC'));
    $timezones = \DateTimeZone::listIdentifiers();
    $sidebarBg = \App\Models\Setting::get('sidebar_bg_color', '#ffffff');
    $headerBg = \App\Models\Setting::get('header_bg_color', '#ffffff');
    $sidebarBgStart = \App\Models\Setting::get('sidebar_bg_start', '');
    $sidebarBgEnd = \App\Models\Setting::get('sidebar_bg_end', '');
    $headerBgStart = \App\Models\Setting::get('header_bg_start', '');
    $headerBgEnd = \App\Models\Setting::get('header_bg_end', '');
    $sidebarTabBg = \App\Models\Setting::get('sidebar_tab_bg_color', '#ffffff');
    $sidebarTabText = \App\Models\Setting::get('sidebar_tab_text_color', '#2a2f45');
    $logoOverlayColor = \App\Models\Setting::get('logo_overlay_color', '');
    $frontendHeaderLogoOverlayColor = \App\Models\Setting::get('frontend_header_logo_overlay_color', '');
    $frontendFooterLogoOverlayColor = \App\Models\Setting::get('frontend_footer_logo_overlay_color', '');
    $frontendHeaderBgColor = \App\Models\Setting::get('frontend_header_bg_color', '#070708');
    $frontendHeaderTextColor = \App\Models\Setting::get('frontend_header_text_color', '#ffffff');
    $frontendFooterBgColor = \App\Models\Setting::get('frontend_footer_bg_color', '#000000');
    $frontendFooterTextColor = \App\Models\Setting::get('frontend_footer_text_color', '#ffffff');
    $frontendFooterBottomText = \App\Models\Setting::get('frontend_footer_bottom_text', '&copy; ' . date('Y') . ' WooFood. ' . __('frontend_all_rights_reserved'));
    $frontendCollapseBgColor = \App\Models\Setting::get('frontend_collapse_bg_color', '#d90f02');
    $frontendCollapseHeadingTextColor = \App\Models\Setting::get('frontend_collapse_heading_text_color', '#ffffff');
    $frontendProductTitleColor = \App\Models\Setting::get('frontend_product_title_color', '#eb3826');
    $frontendProductPriceColor = \App\Models\Setting::get('frontend_product_price_color', '#ff1200');
    $frontendProductDescriptionColor = \App\Models\Setting::get('frontend_product_description_color', '#333333');
    $frontendChooseButtonColor = \App\Models\Setting::get('frontend_choose_button_color', '#ffca27');
    $frontendChooseButtonTextColor = \App\Models\Setting::get('frontend_choose_button_text_color', '#111111');
    $frontendViewCartButtonColor = \App\Models\Setting::get('frontend_view_cart_button_color', '#ffc933');
    $frontendViewCartButtonTextColor = \App\Models\Setting::get('frontend_view_cart_button_text_color', '#161616');
    $frontendCheckoutButtonColor = \App\Models\Setting::get('frontend_checkout_button_color', '#151515');
    $frontendCheckoutButtonTextColor = \App\Models\Setting::get('frontend_checkout_button_text_color', '#ffffff');
    $mapProvider = \App\Models\Setting::get('map_provider', 'leaflet');
    $googleMapsApiKey = \App\Models\Setting::get('google_maps_api_key', '');
    $pickupSchedule = json_decode(\App\Models\Setting::get('pickup_schedule_json', '{}'), true);
    $deliverySchedule = json_decode(\App\Models\Setting::get('delivery_schedule_json', '{}'), true);
    $openingHoursSchedule = json_decode(\App\Models\Setting::get('opening_hours_json', '{}'), true);
    $paymentMethods = \App\Models\Setting::paymentMethods();
    $cookieBannerConfig = \App\Models\Setting::cookieBannerConfig();
    $cookieBannerEnabled = $cookieBannerConfig['enabled'];
    $cookieCategories = $cookieBannerConfig['categories'];
    if (empty($paymentMethods)) {
        $paymentMethods = [
            [
                'id' => 'stripe',
                'title' => 'Stripe',
                'code' => 'stripe',
                'type' => 'stripe',
                'description' => '',
                'is_active' => false,
                'config' => [
                    'environment' => 'sandbox',
                    'live_public_key' => '',
                    'live_secret_key' => '',
                    'sandbox_public_key' => '',
                    'sandbox_secret_key' => '',
                ],
            ],
            [
                'id' => 'cash_on_delivery',
                'title' => 'Cash on Delivery',
                'code' => 'cash_on_delivery',
                'type' => 'cash_on_delivery',
                'description' => '',
                'is_active' => false,
                'config' => [],
            ],
            [
                'id' => 'bank_account',
                'title' => 'Bank Account',
                'code' => 'bank_account',
                'type' => 'bank_account',
                'description' => '',
                'is_active' => false,
                'config' => [
                    'account_title' => '',
                    'iban' => '',
                    'branch_name' => '',
                    'account_number' => '',
                ],
            ],
            [
                'id' => 'custom',
                'title' => 'Custom Method',
                'code' => 'custom_method',
                'type' => 'custom',
                'description' => '',
                'is_active' => false,
                'config' => [
                    'environment' => 'sandbox',
                    'live_public_key' => '',
                    'live_private_key' => '',
                    'sandbox_public_key' => '',
                    'sandbox_private_key' => '',
                ],
            ],
        ];
    }
    $pickupWeekDays = [
        'monday' => __('pickup_day_monday'),
        'tuesday' => __('pickup_day_tuesday'),
        'wednesday' => __('pickup_day_wednesday'),
        'thursday' => __('pickup_day_thursday'),
        'friday' => __('pickup_day_friday'),
        'saturday' => __('pickup_day_saturday'),
        'sunday' => __('pickup_day_sunday'),
    ];
    $deliveryWeekDays = [
        'monday' => __('delivery_day_monday'),
        'tuesday' => __('delivery_day_tuesday'),
        'wednesday' => __('delivery_day_wednesday'),
        'thursday' => __('delivery_day_thursday'),
        'friday' => __('delivery_day_friday'),
        'saturday' => __('delivery_day_saturday'),
        'sunday' => __('delivery_day_sunday'),
    ];
    $openingHoursWeekDays = [
        'monday' => __('pickup_day_monday'),
        'tuesday' => __('pickup_day_tuesday'),
        'wednesday' => __('pickup_day_wednesday'),
        'thursday' => __('pickup_day_thursday'),
        'friday' => __('pickup_day_friday'),
        'saturday' => __('pickup_day_saturday'),
        'sunday' => __('pickup_day_sunday'),
    ];
@endphp
<script>
    window.gymCurrency = {
        label: @json($currencyLabel),
        code: @json($currencyCode),
        rate: @json($currencyRate),
        baseLabel: @json($baseLabel)
    };
    window.settingsI18n = {
        addDuration: @json(__('settings_add_duration')),
        addSession: @json(__('settings_add_session')),
        newMembership: @json(__('settings_new_membership')),
        durationOneMonth: @json(__('settings_duration_1_month')),
        newDuration: @json(__('settings_new_duration')),
        newCategory: @json(__('settings_new_category')),
        newTrainerType: @json(__('settings_new_trainer_type')),
        newSessionPackage: @json(__('settings_new_session_package')),
        durationDays: @json(__('settings_duration_days')),
        priceBaseLabel: @json(__('settings_price_base_label')),
        pricePlaceholder: @json(__('settings_price_placeholder')),
        pickupAddTime: @json(__('pickup_add_time')),
        deliveryAddTime: @json(__('delivery_add_time')),
        openingHoursAddTime: @json(__('opening_hours_add_time'))
    };
</script>
<style>
    .settings-save-float {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1050;
    }
    .settings-save-float .btn {
        border-radius: 999px;
        padding: 12px 20px;
        box-shadow: 0 12px 24px rgba(15, 155, 142, 0.25);
    }
    .settings-tabs {
        position: sticky;
        top: 72px;
        z-index: 1020;
        background: #ffffff;
        padding: 12px 12px;
        border-radius: 16px;
        border: 1px solid #eef2f7;
        box-shadow: 0 10px 24px rgba(18, 38, 63, 0.08);
        margin-bottom: 20px;
        gap: 10px;
    }
    .settings-tabs .nav-link {
        border-radius: 999px;
        padding: 8px 16px;
        font-weight: 700;
        background: #f5f7fb;
        color: {{ $themeAccent }};
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .settings-tabs .nav-link:hover,
    .settings-tabs .nav-link:focus {
        background: rgba(15, 118, 110, 0.08);
        border-color: {{ $themeSecondary }};
        color: {{ $themePrimary }};
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(15, 118, 110, 0.18);
    }
    .settings-tabs .nav-link.active {
        background: linear-gradient(135deg, {{ $themePrimary }}, {{ $themeSecondary }});
        color: {{ $sidebarDashboardTextColor }};
        border-color: transparent;
        box-shadow: 0 10px 18px rgba(15, 118, 110, 0.25);
    }
    .settings-section {
        scroll-margin-top: 110px;
        margin-left: 0.75rem;
        margin-right: 0.75rem;
    }
    .card-body > .settings-section,
    .card-body > hr {
        margin-left: 0.75rem;
        margin-right: 0.75rem;
    }
    .card-body > .settings-section.row {
        --bs-gutter-x: 1.5rem;
    }
    .pickup-day-card,
    .delivery-day-card,
    .opening-hours-day-card {
        height: 100%;
        border: 1px solid #e9eef5;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #fbfcff);
        box-shadow: 0 12px 30px rgba(18, 38, 63, 0.06);
        padding: 18px;
    }
    .pickup-day-head,
    .delivery-day-head,
    .opening-hours-day-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .pickup-day-title,
    .delivery-day-title,
    .opening-hours-day-title {
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }
    .pickup-holiday-toggle,
    .delivery-holiday-toggle,
    .opening-hours-holiday-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        color: #556070;
    }
    .pickup-slot-list,
    .delivery-slot-list,
    .opening-hours-slot-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .pickup-slot-row,
    .delivery-slot-row,
    .opening-hours-slot-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pickup-slot-row .form-control,
    .delivery-slot-row .form-control,
    .opening-hours-slot-row .form-control {
        min-height: 42px;
    }
    .pickup-day-card.is-holiday .pickup-slot-wrap,
    .delivery-day-card.is-holiday .delivery-slot-wrap,
    .opening-hours-day-card.is-holiday .opening-hours-slot-wrap {
        opacity: 0.5;
        pointer-events: none;
    }
    .pickup-day-note,
    .delivery-day-note,
    .opening-hours-day-note {
        font-size: 0.84rem;
        color: #748092;
        margin-top: 10px;
    }
    .pickup-holiday-badge,
    .delivery-holiday-badge,
    .opening-hours-holiday-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(220, 53, 69, 0.12);
        color: #b42318;
        font-size: 0.8rem;
        font-weight: 800;
    }
    .payment-method-card {
        height: 100%;
        border: 1px solid #e8edf5;
        border-radius: 20px;
        background: linear-gradient(180deg, #ffffff, #fbfcff);
        box-shadow: 0 12px 30px rgba(18, 38, 63, 0.06);
        padding: 20px;
    }
    .payment-method-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }
    .payment-method-title {
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }
    .payment-method-subtitle {
        font-size: 0.84rem;
        color: #7b8795;
        margin-top: 4px;
    }
    .payment-method-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .payment-method-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #4f5b67;
        white-space: nowrap;
    }
    .payment-method-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .payment-method-grid .full-span {
        grid-column: 1 / -1;
    }
    .payment-method-config {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed #dde5ef;
    }
    .payment-method-config-note {
        padding: 12px 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e6edf5;
        color: #66758a;
        font-size: 0.88rem;
        line-height: 1.6;
    }
    .payment-method-empty {
        padding: 30px 24px;
        border: 1px dashed #d6deea;
        border-radius: 18px;
        text-align: center;
        color: #7c8b9d;
        background: #fbfcff;
    }
    @media (max-width: 767.98px) {
        .payment-method-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header pb-0">
                        <h4>{{ __('system_settings') }}</h4>
                    </div>
                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                        @csrf
                        <div class="card-body">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            @php $guestMode = env('GUEST_MODE') === 'on'; @endphp
                            @if ($guestMode)
                                <div class="alert alert-warning">
                                    {{ __('settings_guest_mode_block') }}
                                </div>
                            @endif
                            <ul class="nav nav-pills gap-2 flex-wrap settings-tabs">
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-branding">{{ __('settings_tab_branding') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-theme-colors">{{ __('settings_tab_theme') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-login-text">{{ __('settings_tab_login_text') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-home-page">{{ __('settings_tab_home_page') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-pickup-schedule">{{ __('settings_tab_pickup_schedule') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-delivery-schedule">{{ __('settings_tab_delivery_schedule') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-opening-hours">{{ __('settings_tab_opening_hours') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-map-services">{{ __('settings_tab_map_services') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-payment-methods">{{ __('settings_tab_payment_methods') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-cookie-settings">Cookie Settings</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-meta-tags">{{ __('settings_tab_meta_tags') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-smtp">{{ __('settings_tab_smtp') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#settings-timezone">{{ __('settings_tab_timezone') }}</a>
                                </li>
                                {{-- <li class="nav-item">
                                    <a class="nav-link" href="#settings-license">{{ __('settings_tab_license') }}</a>
                                </li> --}}
                            </ul>
                            <div class="settings-section" id="settings-branding">
                                <fieldset {{ $guestMode ? 'disabled' : '' }}>
                                <div class="row">
                                <div class="col-sm-12 mb-4">
                                    <label class="form-label fw-bold">{{ __('footer_text') }}</label>
                                    <input type="text" name="footer_text" class="form-control"
                                           value="{{ \App\Models\Setting::get('footer_text') }}"
                                           placeholder="e.g. Copyright 2026 Â© Sky Fitness Gym" {{ $guestMode ? 'disabled' : '' }}>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('frontend_footer_address_line_1') }}</label>
                                    <input type="text" name="frontend_footer_address_line_1" class="form-control"
                                           value="{{ \App\Models\Setting::get('frontend_footer_address_line_1', '87 Lexington Street') }}"
                                           placeholder="87 Lexington Street" {{ $guestMode ? 'disabled' : '' }}>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('frontend_footer_address_line_2') }}</label>
                                    <input type="text" name="frontend_footer_address_line_2" class="form-control"
                                           value="{{ \App\Models\Setting::get('frontend_footer_address_line_2', '15534 New York') }}"
                                           placeholder="15534 New York" {{ $guestMode ? 'disabled' : '' }}>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('frontend_footer_phone') }}</label>
                                    <input type="text" name="frontend_footer_phone" class="form-control"
                                           value="{{ \App\Models\Setting::get('frontend_footer_phone', '+1 555 123 4567') }}"
                                           placeholder="+1 555 123 4567" {{ $guestMode ? 'disabled' : '' }}>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('login_logo') }}</label>
                                    <input type="file" name="login_logo" class="form-control mb-2" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('login_logo', 'public/assets/images/logo/logo.png')) }}"
                                            style="max-height: 80px; width: auto;">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('admin_logo') }}</label>
                                    <input type="file" name="admin_logo" class="form-control mb-2" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('admin_logo', 'public/assets/images/logo/logo_dark.png')) }}"
                                             style="max-height: 80px; width: auto;">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_header_logo') }}</label>
                                    <input type="file" name="frontend_header_logo" class="form-control mb-2" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('frontend_header_logo', 'assets/images/logo/logo.png')) }}"
                                             style="max-height: 80px; width: auto;">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_footer_logo') }}</label>
                                    <input type="file" name="frontend_footer_logo" class="form-control mb-2" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('frontend_footer_logo', 'assets/images/logo/logo_dark.png')) }}"
                                             style="max-height: 80px; width: auto;">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('login_background_image') }}</label>
                                    <input type="file" name="login_bg_image" class="form-control mb-2" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('login_bg_image', 'public/assets/images/login/bg.jpg')) }}"
                                             style="max-height: 80px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_background_image') }}</label>
                                    <input type="file" name="frontend_background_image" class="form-control mb-2" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('frontend_background_image', 'assets/images/login/login_image.jpg')) }}"
                                             style="max-height: 80px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_favicon') }}</label>
                                    <input type="file" name="favicon" class="form-control mb-2" accept="image/png,image/x-icon,image/vnd.microsoft.icon" {{ $guestMode ? 'disabled' : '' }}>
                                    <div class="preview-box border p-2 text-center bg-light">
                                        <img src="{{ asset('public/' .\App\Models\Setting::get('favicon', 'assets/images/favicon.png')) }}"
                                             style="max-height: 48px; width: auto;">
                                    </div>
                                </div>


                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_sidebar_bg_gradient') }}</label>
                                    <div class="input-group mb-2">
                                        <input type="color" name="sidebar_bg_start" class="form-control form-control-color"
                                               value="{{ $sidebarBgStart ?: $sidebarBg }}">
                                        <input type="text" name="sidebar_bg_start_text" class="form-control"
                                               value="{{ $sidebarBgStart ?: $sidebarBg }}" placeholder="#ffffff">
                                    </div>
                                    <div class="input-group">
                                        <input type="color" name="sidebar_bg_end" class="form-control form-control-color"
                                               value="{{ $sidebarBgEnd ?: $sidebarBg }}">
                                        <input type="text" name="sidebar_bg_end_text" class="form-control"
                                               value="{{ $sidebarBgEnd ?: $sidebarBg }}" placeholder="#ffffff">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_header_bg_gradient') }}</label>
                                    <div class="input-group mb-2">
                                        <input type="color" name="header_bg_start" class="form-control form-control-color"
                                               value="{{ $headerBgStart ?: $headerBg }}">
                                        <input type="text" name="header_bg_start_text" class="form-control"
                                               value="{{ $headerBgStart ?: $headerBg }}" placeholder="#ffffff">
                                    </div>
                                    <div class="input-group">
                                        <input type="color" name="header_bg_end" class="form-control form-control-color"
                                               value="{{ $headerBgEnd ?: $headerBg }}">
                                        <input type="text" name="header_bg_end_text" class="form-control"
                                               value="{{ $headerBgEnd ?: $headerBg }}" placeholder="#ffffff">
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_sidebar_tab_bg_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="sidebar_tab_bg_color" class="form-control form-control-color"
                                               value="{{ $sidebarTabBg }}">
                                        <input type="text" name="sidebar_tab_bg_color_text" class="form-control"
                                               value="{{ $sidebarTabBg }}" placeholder="#ffffff">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_sidebar_tab_text_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="sidebar_tab_text_color" class="form-control form-control-color"
                                               value="{{ $sidebarTabText }}">
                                        <input type="text" name="sidebar_tab_text_color_text" class="form-control"
                                               value="{{ $sidebarTabText }}" placeholder="#2a2f45">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_logo_overlay_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="logo_overlay_color" class="form-control form-control-color"
                                               value="{{ $logoOverlayColor ?: '#ffffff' }}">
                                        <input type="text" name="logo_overlay_color_text" class="form-control"
                                               value="{{ $logoOverlayColor }}" placeholder="#ffffff">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_header_logo_overlay_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="frontend_header_logo_overlay_color" class="form-control form-control-color"
                                               value="{{ $frontendHeaderLogoOverlayColor ?: '#ffffff' }}">
                                        <input type="text" name="frontend_header_logo_overlay_color_text" class="form-control"
                                               value="{{ $frontendHeaderLogoOverlayColor }}" placeholder="#ffffff">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_footer_logo_overlay_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="frontend_footer_logo_overlay_color" class="form-control form-control-color"
                                               value="{{ $frontendFooterLogoOverlayColor ?: '#ffffff' }}">
                                        <input type="text" name="frontend_footer_logo_overlay_color_text" class="form-control"
                                               value="{{ $frontendFooterLogoOverlayColor }}" placeholder="#ffffff">
                                    </div>
                                </div>
                                </div>
                                </fieldset>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-home-page">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_home_page') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_home_page_desc') }}</p>
                                </div>
                                <div class="col-12">
                                    <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
                                        <div class="row">
                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_header_bg_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_header_bg_color" class="form-control form-control-color" value="{{ $frontendHeaderBgColor }}">
                                                    <input type="text" name="frontend_header_bg_color_text" class="form-control" value="{{ $frontendHeaderBgColor }}" placeholder="#070708">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_header_text_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_header_text_color" class="form-control form-control-color" value="{{ $frontendHeaderTextColor }}">
                                                    <input type="text" name="frontend_header_text_color_text" class="form-control" value="{{ $frontendHeaderTextColor }}" placeholder="#ffffff">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_footer_bg_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_footer_bg_color" class="form-control form-control-color" value="{{ $frontendFooterBgColor }}">
                                                    <input type="text" name="frontend_footer_bg_color_text" class="form-control" value="{{ $frontendFooterBgColor }}" placeholder="#000000">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_footer_text_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_footer_text_color" class="form-control form-control-color" value="{{ preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $frontendFooterTextColor) ? $frontendFooterTextColor : '#ffffff' }}">
                                                    <input type="text" name="frontend_footer_text_color_text" class="form-control" value="{{ $frontendFooterTextColor }}" placeholder="#ffffff">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_footer_bottom_text') }}</label>
                                                <input type="text" name="frontend_footer_bottom_text" class="form-control" value="{{ $frontendFooterBottomText }}" placeholder="&copy; {{ date('Y') }} WooFood. {{ __('frontend_all_rights_reserved') }}">
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_collapse_bg_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_collapse_bg_color" class="form-control form-control-color" value="{{ $frontendCollapseBgColor }}">
                                                    <input type="text" name="frontend_collapse_bg_color_text" class="form-control" value="{{ $frontendCollapseBgColor }}" placeholder="#d90f02">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_collapse_heading_text_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_collapse_heading_text_color" class="form-control form-control-color" value="{{ $frontendCollapseHeadingTextColor }}">
                                                    <input type="text" name="frontend_collapse_heading_text_color_text" class="form-control" value="{{ $frontendCollapseHeadingTextColor }}" placeholder="#ffffff">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_product_title_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_product_title_color" class="form-control form-control-color" value="{{ $frontendProductTitleColor }}">
                                                    <input type="text" name="frontend_product_title_color_text" class="form-control" value="{{ $frontendProductTitleColor }}" placeholder="#eb3826">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_product_price_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_product_price_color" class="form-control form-control-color" value="{{ $frontendProductPriceColor }}">
                                                    <input type="text" name="frontend_product_price_color_text" class="form-control" value="{{ $frontendProductPriceColor }}" placeholder="#ff1200">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_product_description_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_product_description_color" class="form-control form-control-color" value="{{ $frontendProductDescriptionColor }}">
                                                    <input type="text" name="frontend_product_description_color_text" class="form-control" value="{{ $frontendProductDescriptionColor }}" placeholder="#333333">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_choose_button_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_choose_button_color" class="form-control form-control-color" value="{{ $frontendChooseButtonColor }}">
                                                    <input type="text" name="frontend_choose_button_color_text" class="form-control" value="{{ $frontendChooseButtonColor }}" placeholder="#ffca27">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_choose_button_text_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_choose_button_text_color" class="form-control form-control-color" value="{{ $frontendChooseButtonTextColor }}">
                                                    <input type="text" name="frontend_choose_button_text_color_text" class="form-control" value="{{ $frontendChooseButtonTextColor }}" placeholder="#111111">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_view_cart_button_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_view_cart_button_color" class="form-control form-control-color" value="{{ $frontendViewCartButtonColor }}">
                                                    <input type="text" name="frontend_view_cart_button_color_text" class="form-control" value="{{ $frontendViewCartButtonColor }}" placeholder="#ffc933">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_view_cart_button_text_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_view_cart_button_text_color" class="form-control form-control-color" value="{{ $frontendViewCartButtonTextColor }}">
                                                    <input type="text" name="frontend_view_cart_button_text_color_text" class="form-control" value="{{ $frontendViewCartButtonTextColor }}" placeholder="#161616">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_checkout_button_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_checkout_button_color" class="form-control form-control-color" value="{{ $frontendCheckoutButtonColor }}">
                                                    <input type="text" name="frontend_checkout_button_color_text" class="form-control" value="{{ $frontendCheckoutButtonColor }}" placeholder="#151515">
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_frontend_checkout_button_text_color') }}</label>
                                                <div class="input-group">
                                                    <input type="color" name="frontend_checkout_button_text_color" class="form-control form-control-color" value="{{ $frontendCheckoutButtonTextColor }}">
                                                    <input type="text" name="frontend_checkout_button_text_color_text" class="form-control" value="{{ $frontendCheckoutButtonTextColor }}" placeholder="#ffffff">
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-pickup-schedule">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_pickup_schedule') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_pickup_schedule_desc') }}</p>
                                </div>
                                <div class="col-12">
                                    <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
                                        <input type="hidden" name="pickup_schedule_json" id="pickupScheduleJson">
                                        <div class="row g-4" id="pickupScheduleEditor">
                                            @foreach($pickupWeekDays as $dayKey => $dayLabel)
                                                @php
                                                    $daySchedule = is_array($pickupSchedule[$dayKey] ?? null) ? $pickupSchedule[$dayKey] : [];
                                                    $dayHoliday = (bool) ($daySchedule['holiday'] ?? false);
                                                    $dayTimes = collect($daySchedule['times'] ?? [])->filter()->values()->all();
                                                    if (empty($dayTimes)) {
                                                        $dayTimes = [''];
                                                    }
                                                @endphp
                                                <div class="col-xl-6">
                                                    <div class="pickup-day-card {{ $dayHoliday ? 'is-holiday' : '' }}" data-pickup-day="{{ $dayKey }}">
                                                        <div class="pickup-day-head">
                                                            <div>
                                                                <h6 class="pickup-day-title">{{ $dayLabel }}</h6>
                                                                <div class="pickup-day-note">{{ __('settings_pickup_day_note') }}</div>
                                                            </div>
                                                            <label class="pickup-holiday-toggle">
                                                                <input type="checkbox" class="form-check-input mt-0 pickup-holiday-checkbox" {{ $dayHoliday ? 'checked' : '' }}>
                                                                <span>{{ __('pickup_holiday') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class="pickup-slot-wrap">
                                                            <div class="pickup-slot-list">
                                                                @foreach($dayTimes as $slotTime)
                                                                    <div class="pickup-slot-row">
                                                                        <input type="time" class="form-control pickup-time-input" value="{{ $slotTime }}">
                                                                        <button type="button" class="btn btn-outline-danger btn-sm pickup-remove-slot">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3 pickup-add-slot">
                                                                <i class="fa fa-plus me-1"></i>{{ __('pickup_add_time') }}
                                                            </button>
                                                        </div>
                                                        <div class="pickup-day-note mt-3">
                                                            <span class="pickup-holiday-badge {{ $dayHoliday ? '' : 'd-none' }}">
                                                                <i class="fa fa-mug-saucer"></i>{{ __('pickup_holiday_active') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-delivery-schedule">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_delivery_schedule') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_delivery_schedule_desc') }}</p>
                                </div>
                                <div class="col-12">
                                    <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
                                        <input type="hidden" name="delivery_schedule_json" id="deliveryScheduleJson">
                                        <div class="row g-4" id="deliveryScheduleEditor">
                                            @foreach($deliveryWeekDays as $dayKey => $dayLabel)
                                                @php
                                                    $daySchedule = is_array($deliverySchedule[$dayKey] ?? null) ? $deliverySchedule[$dayKey] : [];
                                                    $dayHoliday = (bool) ($daySchedule['holiday'] ?? false);
                                                    $dayTimes = collect($daySchedule['times'] ?? [])->filter()->values()->all();
                                                    if (empty($dayTimes)) {
                                                        $dayTimes = [''];
                                                    }
                                                @endphp
                                                <div class="col-xl-6">
                                                    <div class="delivery-day-card {{ $dayHoliday ? 'is-holiday' : '' }}" data-delivery-day="{{ $dayKey }}">
                                                        <div class="delivery-day-head">
                                                            <div>
                                                                <h6 class="delivery-day-title">{{ $dayLabel }}</h6>
                                                                <div class="delivery-day-note">{{ __('settings_delivery_day_note') }}</div>
                                                            </div>
                                                            <label class="delivery-holiday-toggle">
                                                                <input type="checkbox" class="form-check-input mt-0 delivery-holiday-checkbox" {{ $dayHoliday ? 'checked' : '' }}>
                                                                <span>{{ __('delivery_holiday') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class="delivery-slot-wrap">
                                                            <div class="delivery-slot-list">
                                                                @foreach($dayTimes as $slotTime)
                                                                    <div class="delivery-slot-row">
                                                                        <input type="time" class="form-control delivery-time-input" value="{{ $slotTime }}">
                                                                        <button type="button" class="btn btn-outline-danger btn-sm delivery-remove-slot">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3 delivery-add-slot">
                                                                <i class="fa fa-plus me-1"></i>{{ __('delivery_add_time') }}
                                                            </button>
                                                        </div>
                                                        <div class="delivery-day-note mt-3">
                                                            <span class="delivery-holiday-badge {{ $dayHoliday ? '' : 'd-none' }}">
                                                                <i class="fa fa-truck-fast"></i>{{ __('delivery_holiday_active') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-opening-hours">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_opening_hours') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_opening_hours_desc') }}</p>
                                </div>
                                <div class="col-12">
                                    <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
                                        <input type="hidden" name="opening_hours_json" id="openingHoursJson">
                                        <div class="row g-4" id="openingHoursEditor">
                                            @foreach($openingHoursWeekDays as $dayKey => $dayLabel)
                                                @php
                                                    $daySchedule = is_array($openingHoursSchedule[$dayKey] ?? null) ? $openingHoursSchedule[$dayKey] : [];
                                                    $dayHoliday = (bool) ($daySchedule['holiday'] ?? false);
                                                    $dayTimes = collect($daySchedule['times'] ?? [])->filter()->values()->all();
                                                    if (empty($dayTimes)) {
                                                        $dayTimes = [''];
                                                    }
                                                @endphp
                                                <div class="col-xl-6">
                                                    <div class="opening-hours-day-card {{ $dayHoliday ? 'is-holiday' : '' }}" data-opening-hours-day="{{ $dayKey }}">
                                                        <div class="opening-hours-day-head">
                                                            <div>
                                                                <h6 class="opening-hours-day-title">{{ $dayLabel }}</h6>
                                                                <div class="opening-hours-day-note">{{ __('settings_opening_hours_day_note') }}</div>
                                                            </div>
                                                            <label class="opening-hours-holiday-toggle">
                                                                <input type="checkbox" class="form-check-input mt-0 opening-hours-holiday-checkbox" {{ $dayHoliday ? 'checked' : '' }}>
                                                                <span>{{ __('opening_hours_closed_label') }}</span>
                                                            </label>
                                                        </div>
                                                        <div class="opening-hours-slot-wrap">
                                                            <div class="opening-hours-slot-list">
                                                                @foreach($dayTimes as $slotTime)
                                                                    <div class="opening-hours-slot-row">
                                                                        <input type="time" class="form-control opening-hours-time-input" value="{{ $slotTime }}">
                                                                        <button type="button" class="btn btn-outline-danger btn-sm opening-hours-remove-slot">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <button type="button" class="btn btn-outline-primary btn-sm mt-3 opening-hours-add-slot">
                                                                <i class="fa fa-plus me-1"></i>{{ __('opening_hours_add_time') }}
                                                            </button>
                                                        </div>
                                                        <div class="opening-hours-day-note mt-3">
                                                            <span class="opening-hours-holiday-badge {{ $dayHoliday ? '' : 'd-none' }}">
                                                                <i class="fa fa-door-closed"></i>{{ __('opening_hours_closed_today') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-map-services">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_map_services') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_map_services_desc') }}</p>
                                </div>
                                <div class="col-12">
                                    <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
                                        <div class="row">
                                            <div class="col-xl-4 col-md-6 mb-3">
                                                <label class="form-label fw-bold">{{ __('settings_map_provider') }}</label>
                                                <select name="map_provider" id="map_provider" class="form-select">
                                                    <option value="leaflet" {{ $mapProvider === 'leaflet' ? 'selected' : '' }}>{{ __('settings_map_provider_leaflet') }}</option>
                                                    <option value="google" {{ $mapProvider === 'google' ? 'selected' : '' }}>{{ __('settings_map_provider_google') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-xl-8 col-md-6 mb-3" id="googleMapsKeyWrap">
                                                <label class="form-label fw-bold">{{ __('settings_google_maps_api_key') }}</label>
                                                <input type="text" name="google_maps_api_key" id="google_maps_api_key" class="form-control" value="{{ $googleMapsApiKey }}" placeholder="{{ __('settings_google_maps_api_key_placeholder') }}">
                                                <div class="form-text">{{ __('settings_google_maps_api_key_note') }}</div>
                                            </div>
                                            <div class="col-12">
                                                <div class="alert alert-light border mb-0">
                                                    <strong>{{ __('settings_map_provider_leaflet') }}:</strong> {{ __('settings_map_provider_leaflet_note') }}<br>
                                                    <strong>{{ __('settings_map_provider_google') }}:</strong> {{ __('settings_map_provider_google_note') }}
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-payment-methods">
                                <div class="col-12 mb-2">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                        <div>
                                            <h5 class="mb-1">{{ __('settings_payment_methods') }}</h5>
                                            <p class="text-muted mb-0">{{ __('settings_payment_methods_desc') }}</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addPaymentMethodBtn" {{ $guestMode ? 'disabled' : '' }}>
                                            <i class="fa fa-plus me-1"></i>{{ __('payment_method_add_new') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <fieldset class="border-0 p-0 m-0" {{ $guestMode ? 'disabled' : '' }}>
                                        <input type="hidden" name="payment_methods_json" id="paymentMethodsJson">
                                        <div class="row g-4" id="paymentMethodsEditor">
                                            @foreach($paymentMethods as $method)
                                                @php
                                                    $methodType = $method['type'] ?? 'custom';
                                                    $methodConfig = is_array($method['config'] ?? null) ? $method['config'] : [];
                                                @endphp
                                                <div class="col-xl-6 payment-method-item">
                                                    <div class="payment-method-card" data-payment-method>
                                                        <div class="payment-method-head">
                                                            <div>
                                                                <h6 class="payment-method-title">{{ $method['title'] ?: __('payment_method_title') }}</h6>
                                                                <div class="payment-method-subtitle">{{ __('payment_method_note_dynamic') }}</div>
                                                            </div>
                                                            <div class="payment-method-actions">
                                                                <label class="payment-method-toggle">
                                                                    <input type="checkbox" class="form-check-input mt-0 payment-method-active" {{ !empty($method['is_active']) ? 'checked' : '' }}>
                                                                    <span>{{ __('payment_method_active') }}</span>
                                                                </label>
                                                                <button type="button" class="btn btn-outline-danger btn-sm payment-method-remove">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="payment-method-grid">
                                                            <div>
                                                                <label class="form-label fw-bold">{{ __('payment_method_title') }}</label>
                                                                <input type="text" class="form-control payment-method-title-input" value="{{ $method['title'] ?? '' }}" placeholder="{{ __('payment_method_placeholder') }}">
                                                            </div>
                                                            <div>
                                                                <label class="form-label fw-bold">{{ __('payment_method_code') }}</label>
                                                                <input type="text" class="form-control payment-method-code-input" value="{{ $method['code'] ?? '' }}" placeholder="{{ __('payment_method_code_placeholder') }}">
                                                            </div>
                                                            <div>
                                                                <label class="form-label fw-bold">{{ __('payment_method_type') }}</label>
                                                                <select class="form-select payment-method-type">
                                                                    <option value="stripe" {{ $methodType === 'stripe' ? 'selected' : '' }}>{{ __('payment_method_stripe') }}</option>
                                                                    <option value="cash_on_delivery" {{ $methodType === 'cash_on_delivery' ? 'selected' : '' }}>{{ __('payment_method_cash_on_delivery') }}</option>
                                                                    <option value="bank_account" {{ $methodType === 'bank_account' ? 'selected' : '' }}>{{ __('payment_method_bank_account') }}</option>
                                                                    <option value="custom" {{ $methodType === 'custom' ? 'selected' : '' }}>{{ __('payment_method_custom') }}</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="form-label fw-bold">{{ __('payment_method_status_label') }}</label>
                                                                <input type="text" class="form-control payment-method-type-label" value="{{ __('payment_method_' . $methodType) }}" readonly>
                                                            </div>
                                                            <div class="full-span">
                                                                <label class="form-label fw-bold">{{ __('payment_method_description') }}</label>
                                                                <textarea class="form-control payment-method-description-input" rows="2" placeholder="{{ __('payment_method_description_placeholder') }}">{{ $method['description'] ?? '' }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="payment-method-config">
                                                            <div class="payment-method-fields payment-method-fields-stripe {{ $methodType === 'stripe' ? '' : 'd-none' }}">
                                                                <div class="payment-method-grid">
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_environment') }}</label>
                                                                        <select class="form-select payment-method-environment">
                                                                            <option value="sandbox" {{ ($methodConfig['environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>{{ __('payment_method_sandbox') }}</option>
                                                                            <option value="live" {{ ($methodConfig['environment'] ?? '') === 'live' ? 'selected' : '' }}>{{ __('payment_method_live') }}</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="payment-method-config-note">
                                                                        {{ __('payment_method_stripe_note') }}
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_live_public_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-live-public-key" value="{{ $methodConfig['live_public_key'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_live_secret_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-live-secret-key" value="{{ $methodConfig['live_secret_key'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_sandbox_public_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-sandbox-public-key" value="{{ $methodConfig['sandbox_public_key'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_sandbox_secret_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-sandbox-secret-key" value="{{ $methodConfig['sandbox_secret_key'] ?? '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="payment-method-fields payment-method-fields-cash_on_delivery {{ $methodType === 'cash_on_delivery' ? '' : 'd-none' }}">
                                                                <div class="payment-method-config-note">{{ __('payment_method_cod_note') }}</div>
                                                            </div>
                                                            <div class="payment-method-fields payment-method-fields-bank_account {{ $methodType === 'bank_account' ? '' : 'd-none' }}">
                                                                <div class="payment-method-grid">
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_account_title') }}</label>
                                                                        <input type="text" class="form-control payment-method-account-title" value="{{ $methodConfig['account_title'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_iban') }}</label>
                                                                        <input type="text" class="form-control payment-method-iban" value="{{ $methodConfig['iban'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_branch_name') }}</label>
                                                                        <input type="text" class="form-control payment-method-branch-name" value="{{ $methodConfig['branch_name'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_account_number') }}</label>
                                                                        <input type="text" class="form-control payment-method-account-number" value="{{ $methodConfig['account_number'] ?? '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="payment-method-fields payment-method-fields-custom {{ $methodType === 'custom' ? '' : 'd-none' }}">
                                                                <div class="payment-method-grid">
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_environment') }}</label>
                                                                        <select class="form-select payment-method-custom-environment">
                                                                            <option value="sandbox" {{ ($methodConfig['environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>{{ __('payment_method_sandbox') }}</option>
                                                                            <option value="live" {{ ($methodConfig['environment'] ?? '') === 'live' ? 'selected' : '' }}>{{ __('payment_method_live') }}</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="payment-method-config-note">
                                                                        {{ __('payment_method_custom_note') }}
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_live_public_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-custom-live-public-key" value="{{ $methodConfig['live_public_key'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_live_private_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-custom-live-private-key" value="{{ $methodConfig['live_private_key'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_sandbox_public_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-custom-sandbox-public-key" value="{{ $methodConfig['sandbox_public_key'] ?? '' }}">
                                                                    </div>
                                                                    <div>
                                                                        <label class="form-label fw-bold">{{ __('payment_method_sandbox_private_key') }}</label>
                                                                        <input type="text" class="form-control payment-method-custom-sandbox-private-key" value="{{ $methodConfig['sandbox_private_key'] ?? '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="payment-method-empty d-none" id="paymentMethodsEmpty">
                                            {{ __('payment_method_empty_state') }}
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                            {{--
                                <div class="row">
                                <div class="col-12">
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                                            <div>
                                                <h6 class="mb-1">{{ __('settings_color_suggestions') }}</h6>
                                                <p class="text-muted mb-0">{{ __('settings_color_suggestions_desc') }}</p>
                                            </div>
                                            <div class="d-flex flex-column align-items-start align-items-sm-end gap-1">
                                                <span class="text-muted small fw-semibold">{{ __('settings_color_tone') }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span id="colorToneLabel" class="badge rounded-pill text-bg-light border">{{ __('settings_color_tone_neutral') }}</span>
                                                    <input type="range" class="form-range" id="colorSuggestionTone" min="0" max="100" value="50" style="width: 180px;">
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-3">{{ __('settings_color_tone_hint') }}</p>
                                        <div id="colorSuggestionCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="12000">
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <div class="row g-3">
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#e0f2fe;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f0f9ff;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#E0F2FE â†’ #F0F9FF</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#eef2ff;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f8fafc;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#EEF2FF â†’ #F8FAFC</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fff7ed;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fffbeb;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#FFF7ED â†’ #FFFBEB</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fef3c7;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fef9c3;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#FEF3C7 â†’ #FEF9C3</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fdf2f8;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fce7f3;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#FDF2F8 â†’ #FCE7F3</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#e0f2fe;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#eff6ff;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#E0F2FE â†’ #EFF6FF</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="carousel-item">
                                                    <div class="row g-3">
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#e2e8f0;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f1f5f9;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#E2E8F0 â†’ #F1F5F9</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#e7e5ff;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f5f3ff;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#E7E5FF â†’ #F5F3FF</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#ecfccb;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f7fee7;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#ECFCCB â†’ #F7FEE7</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#dbeafe;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#eff6ff;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#DBEAFE â†’ #EFF6FF</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fee2e2;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fef2f2;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#FEE2E2 â†’ #FEF2F2</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f3e8ff;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#faf5ff;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#F3E8FF â†’ #FAF5FF</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="carousel-item">
                                                    <div class="row g-3">
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0f172a;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1f2937;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0F172A â†’ #1F2937</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#111827;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0b1120;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#111827 â†’ #0B1120</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1e293b;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#334155;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#1E293B â†’ #334155</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0f172a;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#111827;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0F172A â†’ #111827</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0b1220;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1e1b4b;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0B1220 â†’ #1E1B4B</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0f172a;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1f2937;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0F172A â†’ #1F2937</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="carousel-item">
                                                    <div class="row g-3">
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f0f4ff;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#e9f2ff;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#F0F4FF â†’ #E9F2FF</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fdf4ff;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fff7fb;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#FDF4FF â†’ #FFF7FB</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#ecfeff;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f0fdfa;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#ECFEFF â†’ #F0FDFA</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f0fdf4;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#ecfccb;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#F0FDF4 â†’ #ECFCCB</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f5f5f4;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#fafaf9;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#F5F5F4 â†’ #FAFAF9</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#e0f2f1;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#f0fdfa;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#E0F2F1 â†’ #F0FDFA</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="carousel-item">
                                                    <div class="row g-3">
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#111827;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1f2937;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#111827 â†’ #1F2937</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0f172a;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#111827;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0F172A â†’ #111827</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1f2937;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#374151;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#1F2937 â†’ #374151</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0b1020;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#111827;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0B1020 â†’ #111827</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="p-3 border rounded-3 bg-white">
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <span class="fw-semibold">{{ __('settings_header_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0b1120;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#1e293b;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0B1120 â†’ #1E293B</div>
                                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                                    <span class="fw-semibold">{{ __('settings_sidebar_color') }}</span>
                                                                    <div class="d-flex gap-2">
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#0a0f1a;"></span>
                                                                        <span class="rounded-circle d-inline-block" style="width: 18px; height: 18px; background:#111827;"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-muted small">#0A0F1A â†’ #111827</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#colorSuggestionCarousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#colorSuggestionCarousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                --}}
                            </div>
                            <fieldset {{ $guestMode ? 'disabled' : '' }}>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-theme-colors">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_theme_colors') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_theme_desc') }}</p>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_primary_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="theme_primary" class="form-control form-control-color"
                                               value="{{ \App\Models\Setting::get('theme_primary', '#7367f0') }}">
                                        <input type="text" name="theme_primary_text" class="form-control"
                                               value="{{ \App\Models\Setting::get('theme_primary', '#7367f0') }}"
                                               placeholder="#7367f0">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_secondary_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="theme_secondary" class="form-control form-control-color"
                                               value="{{ \App\Models\Setting::get('theme_secondary', '#00cfe8') }}">
                                        <input type="text" name="theme_secondary_text" class="form-control"
                                               value="{{ \App\Models\Setting::get('theme_secondary', '#00cfe8') }}"
                                               placeholder="#00cfe8">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_accent_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="theme_accent" class="form-control form-control-color"
                                               value="{{ \App\Models\Setting::get('theme_accent', '#0f9b8e') }}">
                                        <input type="text" name="theme_accent_text" class="form-control"
                                               value="{{ \App\Models\Setting::get('theme_accent', '#0f9b8e') }}"
                                               placeholder="#0f9b8e">
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_sidebar_dashboard_text_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="sidebar_dashboard_text_color" class="form-control form-control-color"
                                               value="{{ \App\Models\Setting::get('sidebar_dashboard_text_color', '#ffffff') }}">
                                        <input type="text" name="sidebar_dashboard_text_color_text" class="form-control"
                                               value="{{ \App\Models\Setting::get('sidebar_dashboard_text_color', '#ffffff') }}"
                                               placeholder="#ffffff">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-login-text">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_login_text') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_login_text_desc') }}</p>
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_heading') }}</label>
                                    <input type="text" name="login_heading" class="form-control"
                                           value="{{ \App\Models\Setting::get('login_heading', 'Train Smarter, Track Faster') }}">
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_badge_text') }}</label>
                                    <input type="text" name="login_badge_text" class="form-control"
                                           value="{{ \App\Models\Setting::get('login_badge_text', 'Sky Fitness Gym') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_heading_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="login_heading_color" class="form-control form-control-color"
                                               value="{{ \App\Models\Setting::get('login_heading_color', '#ffffff') }}">
                                        <input type="text" name="login_heading_color_text" class="form-control"
                                               value="{{ \App\Models\Setting::get('login_heading_color', '#ffffff') }}"
                                               placeholder="#ffffff">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_overlay_color') }}</label>
                                    <div class="input-group">
                                        <input type="color" name="login_overlay_color" class="form-control form-control-color"
                                               value="{{ \App\Models\Setting::get('login_overlay_color', '#110c09') }}">
                                        <input type="text" name="login_overlay_color_text" class="form-control"
                                               value="{{ \App\Models\Setting::get('login_overlay_color', '#110c09') }}"
                                               placeholder="#110c09">
                                    </div>
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_description') }}</label>
                                    <textarea name="login_description" class="form-control" rows="3">{{ \App\Models\Setting::get('login_description', 'Streamline daily operations with clear workflows and simple reporting in one place.') }}</textarea>
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_login_heading') }}</label>
                                    <input type="text" name="frontend_login_heading" class="form-control"
                                           value="{{ \App\Models\Setting::get('frontend_login_heading', 'Welcome back to Bazaar Bites') }}">
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_frontend_login_description') }}</label>
                                    <textarea name="frontend_login_description" class="form-control" rows="3">{{ \App\Models\Setting::get('frontend_login_description', 'Log in to continue ordering, review your basket, and move from craving to checkout in just a few taps.') }}</textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_bullet_1') }}</label>
                                    <input type="text" name="login_bullet_1" class="form-control"
                                           value="{{ \App\Models\Setting::get('login_bullet_1', 'Fast member check-ins') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_bullet_2') }}</label>
                                    <input type="text" name="login_bullet_2" class="form-control"
                                           value="{{ \App\Models\Setting::get('login_bullet_2', 'Clean, modern reports') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_login_barcode_toggle') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="hidden" name="login_barcode_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="login_barcode_enabled"
                                               name="login_barcode_enabled" value="1"
                                               {{ \App\Models\Setting::get('login_barcode_enabled', '1') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="login_barcode_enabled">{{ __('settings_login_barcode_toggle_desc') }}</label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            @include('setting.partials.cookie-settings', [
                                'guestMode' => $guestMode,
                                'cookieBannerEnabled' => $cookieBannerEnabled,
                                'cookieBannerConfig' => $cookieBannerConfig,
                                'cookieCategories' => $cookieCategories,
                            ])

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-meta-tags">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_meta_tags') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_meta_tags_desc') }}</p>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_meta_title') }}</label>
                                    <input type="text" name="meta_title" class="form-control"
                                           value="{{ \App\Models\Setting::get('meta_title', 'WooFood') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_meta_author') }}</label>
                                    <input type="text" name="meta_author" class="form-control"
                                           value="{{ \App\Models\Setting::get('meta_author', 'Sky Fitness Gym') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_meta_keywords') }}</label>
                                    <input type="text" name="meta_keywords" class="form-control"
                                           value="{{ \App\Models\Setting::get('meta_keywords', 'tickets, support, dashboard, reporting, operations') }}">
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_meta_description') }}</label>
                                    <textarea name="meta_description" class="form-control" rows="3">{{ \App\Models\Setting::get('meta_description', 'Ticket management system for workflows, users, and reporting.') }}</textarea>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-smtp">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_smtp') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_smtp_desc') }}</p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_host') }}</label>
                                    <input type="text" name="smtp_host" class="form-control"
                                           value="{{ $guestMode ? 'smtp.example.com' : \App\Models\Setting::get('smtp_host') }}" placeholder="smtp.example.com">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_port') }}</label>
                                    <input type="text" name="smtp_port" class="form-control"
                                           value="{{ \App\Models\Setting::get('smtp_port') }}" placeholder="587">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_encryption') }}</label>
                                    <select name="smtp_encryption" class="form-select">
                                        @php $smtpEnc = \App\Models\Setting::get('smtp_encryption', 'tls'); @endphp
                                        <option value="">{{ __('settings_smtp_none') }}</option>
                                        <option value="tls" {{ $smtpEnc === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ $smtpEnc === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_username') }}</label>
                                    <input type="text" name="smtp_username" class="form-control"
                                           value="{{ $guestMode ? 'user@example.com' : \App\Models\Setting::get('smtp_username') }}" placeholder="user@example.com">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_password') }}</label>
                                    <input type="password" name="smtp_password" class="form-control"
                                           value="{{ $guestMode ? '' : \App\Models\Setting::get('smtp_password') }}" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_from_email') }}</label>
                                    <input type="email" name="smtp_from_email" class="form-control"
                                           value="{{ $guestMode ? 'noreply@example.com' : \App\Models\Setting::get('smtp_from_email') }}" placeholder="noreply@example.com">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_smtp_from_name') }}</label>
                                    <input type="text" name="smtp_from_name" class="form-control"
                                           value="{{ \App\Models\Setting::get('smtp_from_name') }}" placeholder="Sky Fitness Gym">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row settings-section" id="settings-timezone">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_timezone') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_timezone_desc') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ __('settings_timezone_label') }}</label>
                                    <select name="timezone" class="form-select select2-timezone" {{ $guestMode ? 'disabled' : '' }}>
                                        @foreach ($timezones as $tz)
                                            <option value="{{ $tz }}" {{ $timezoneValue === $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- <div class="row settings-section" id="settings-license">
                                <div class="col-12 mb-2">
                                    <h5 class="mb-1">{{ __('settings_license') }}</h5>
                                    <p class="text-muted mb-0">{{ __('settings_license_desc') }}</p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('license_client_name') }}</label>
                                    <input type="text" name="license_client_name" class="form-control"
                                           value="{{ \App\Models\Setting::get('license_client_name') }}" placeholder="Sky Fitness Gym">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('license_server_url') }}</label>
                                    <input type="text" name="license_server_url" class="form-control"
                                           value="{{ \App\Models\Setting::get('license_server_url') }}" placeholder="https://license.example.com">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('license_key') }}</label>
                                    <input type="text" name="license_key" class="form-control"
                                           value="{{ \App\Models\Setting::get('license_key') }}" placeholder="LIC-XXXX-XXXX-XXXX">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">{{ __('license_project_key') }}</label>
                                    <input type="text" name="license_project_key" class="form-control"
                                           value="{{ \App\Models\Setting::get('license_project_key') }}" placeholder="PRJ-XXXX-XXXX">
                                </div>
                            </div> --}}

                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit">{{ __('save_settings') }}</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="settings-save-float">
    <button class="btn btn-primary" type="submit" form="settingsForm">
        <i class="fa fa-save me-2"></i>{{ __('save_settings') }}
    </button>
</div>
<script>
    (function() {
        const pairs = [
            { color: 'theme_primary', text: 'theme_primary_text' },
            { color: 'theme_secondary', text: 'theme_secondary_text' },
            { color: 'theme_accent', text: 'theme_accent_text' },
            { color: 'sidebar_bg_color', text: 'sidebar_bg_color_text' },
            { color: 'header_bg_color', text: 'header_bg_color_text' },
            { color: 'sidebar_bg_start', text: 'sidebar_bg_start_text' },
            { color: 'sidebar_bg_end', text: 'sidebar_bg_end_text' },
            { color: 'header_bg_start', text: 'header_bg_start_text' },
            { color: 'header_bg_end', text: 'header_bg_end_text' },
            { color: 'sidebar_tab_bg_color', text: 'sidebar_tab_bg_color_text' },
            { color: 'sidebar_tab_text_color', text: 'sidebar_tab_text_color_text' },
            { color: 'logo_overlay_color', text: 'logo_overlay_color_text' },
            { color: 'frontend_header_logo_overlay_color', text: 'frontend_header_logo_overlay_color_text' },
            { color: 'frontend_footer_logo_overlay_color', text: 'frontend_footer_logo_overlay_color_text' },
            { color: 'frontend_header_bg_color', text: 'frontend_header_bg_color_text' },
            { color: 'frontend_header_text_color', text: 'frontend_header_text_color_text' },
            { color: 'frontend_footer_bg_color', text: 'frontend_footer_bg_color_text' },
            { color: 'frontend_footer_text_color', text: 'frontend_footer_text_color_text' },
            { color: 'frontend_collapse_bg_color', text: 'frontend_collapse_bg_color_text' },
            { color: 'frontend_collapse_heading_text_color', text: 'frontend_collapse_heading_text_color_text' },
            { color: 'frontend_product_title_color', text: 'frontend_product_title_color_text' },
            { color: 'frontend_product_price_color', text: 'frontend_product_price_color_text' },
            { color: 'frontend_product_description_color', text: 'frontend_product_description_color_text' },
            { color: 'frontend_choose_button_color', text: 'frontend_choose_button_color_text' },
            { color: 'frontend_choose_button_text_color', text: 'frontend_choose_button_text_color_text' },
            { color: 'frontend_view_cart_button_color', text: 'frontend_view_cart_button_color_text' },
            { color: 'frontend_view_cart_button_text_color', text: 'frontend_view_cart_button_text_color_text' },
            { color: 'frontend_checkout_button_color', text: 'frontend_checkout_button_color_text' },
            { color: 'frontend_checkout_button_text_color', text: 'frontend_checkout_button_text_color_text' },
            { color: 'card_bg_color', text: 'card_bg_color_text' },
            { color: 'card_text_color', text: 'card_text_color_text' },
            { color: 'sidebar_dashboard_text_color', text: 'sidebar_dashboard_text_color_text' },
            { color: 'login_heading_color', text: 'login_heading_color_text' },
            { color: 'login_overlay_color', text: 'login_overlay_color_text' }
        ];

        const isHex = (val) => /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(val);

        pairs.forEach((pair) => {
            const colorInput = document.querySelector(`input[name="${pair.color}"]`);
            const textInput = document.querySelector(`input[name="${pair.text}"]`);
            if (!colorInput || !textInput) return;

            colorInput.addEventListener('input', () => {
                textInput.value = colorInput.value;
            });

            textInput.addEventListener('input', () => {
                if (isHex(textInput.value)) {
                    colorInput.value = textInput.value;
                    if (pair.color === 'card_bg_color' || pair.color === 'card_text_color') {
                        updateCardPreview();
                    }
                }
            });
        });

        const updateCardPreview = () => {
            const bgInput = document.querySelector('input[name="card_bg_color"]');
            const textColorInput = document.querySelector('input[name="card_text_color"]');
            const titleInput = document.querySelector('input[name="card_title"]');
            const logoChoice = document.querySelector('input[name="card_logo_source"]:checked');
            const preview = document.getElementById('card-preview');
            const titlePreview = document.getElementById('card-title-preview');
            const logoPreview = document.getElementById('card-logo-preview');
            if (!preview || !titlePreview || !logoPreview) return;

            if (bgInput) {
                preview.style.background = bgInput.value;
            }
            if (textColorInput) {
                preview.style.color = textColorInput.value;
            }
            if (titleInput) {
                titlePreview.textContent = titleInput.value || '{{ __('settings_card_default_title') }}';
            }
            if (logoChoice) {
                const source = logoChoice.value;
                const loginLogo = @json(asset('public/' . \App\Models\Setting::get('login_logo', 'public/assets/images/logo/logo.png')));
                const adminLogo = @json(asset('public/' . \App\Models\Setting::get('admin_logo', 'public/assets/images/logo/logo_dark.png')));
                logoPreview.src = source === 'admin' ? adminLogo : loginLogo;
            }
        };

        const updateLayoutPreview = () => {
            const sidebar = document.querySelector('.sidebar-wrapper');
            const header = document.querySelector('.page-header');
            if (!sidebar && !header) return;

            const getValue = (name) => {
                const text = document.querySelector(`input[name="${name}_text"]`);
                const color = document.querySelector(`input[name="${name}"]`);
                if (text && isHex(text.value)) return text.value;
                if (color && isHex(color.value)) return color.value;
                return '';
            };

            const sidebarStart = getValue('sidebar_bg_start');
            const sidebarEnd = getValue('sidebar_bg_end');
            const headerStart = getValue('header_bg_start');
            const headerEnd = getValue('header_bg_end');
            const tabBg = getValue('sidebar_tab_bg_color');
            const tabText = getValue('sidebar_tab_text_color');
            const logoOverlay = getValue('logo_overlay_color');

            if (sidebar) {
                if (sidebarStart && sidebarEnd) {
                    sidebar.style.background = `linear-gradient(180deg, ${sidebarStart} 0%, ${sidebarEnd} 100%)`;
                } else if (sidebarStart) {
                    sidebar.style.background = sidebarStart;
                }
            }

            if (header) {
                if (headerStart && headerEnd) {
                    header.style.background = `linear-gradient(90deg, ${headerStart} 0%, ${headerEnd} 100%)`;
                } else if (headerStart) {
                    header.style.background = headerStart;
                }
            }

            if (tabBg || tabText) {
                document.querySelectorAll('.sidebar-link.link-nav, .sidebar-link.sidebar-title').forEach((el) => {
                    if (tabBg) {
                        el.style.background = tabBg;
                    }
                    if (tabText) {
                        el.style.color = tabText;
                        el.querySelectorAll('i, span, .fa-angle-right, .fa-angle-left, .fa-angle-down, .according-menu i').forEach((node) => {
                            node.style.color = tabText;
                        });
                    }
                });
                document.querySelectorAll('.sidebar-wrapper .according-menu i').forEach((node) => {
                    if (tabText) {
                        node.style.color = tabText;
                    }
                });
                document.querySelectorAll('.sidebar-submenu a').forEach((el) => {
                    if (tabText) {
                        el.style.color = tabText;
                    }
                });
            }

            document.querySelectorAll('.logo-overlay-wrap').forEach((wrap) => {
                wrap.style.setProperty('--logo-color', logoOverlay || 'transparent');
                wrap.classList.toggle('has-overlay', !!logoOverlay);
            });
        };

        const bgInput = document.querySelector('input[name="card_bg_color"]');
        const textColorInput = document.querySelector('input[name="card_text_color"]');
        const titleInput = document.querySelector('input[name="card_title"]');
        const logoRadios = document.querySelectorAll('input[name="card_logo_source"]');
        if (bgInput) bgInput.addEventListener('input', updateCardPreview);
        if (textColorInput) textColorInput.addEventListener('input', updateCardPreview);
        if (titleInput) titleInput.addEventListener('input', updateCardPreview);
        logoRadios.forEach((radio) => radio.addEventListener('change', updateCardPreview));
        updateCardPreview();
        updateLayoutPreview();

        const layoutInputs = [
            'sidebar_bg_start',
            'sidebar_bg_end',
            'header_bg_start',
            'header_bg_end'
        ];
        const extraLayoutInputs = [
            'sidebar_tab_bg_color',
            'sidebar_tab_text_color',
            'logo_overlay_color',
            'frontend_header_logo_overlay_color',
            'frontend_footer_logo_overlay_color',
            'frontend_header_bg_color',
            'frontend_header_text_color',
            'frontend_footer_bg_color',
            'frontend_footer_text_color',
            'frontend_collapse_bg_color',
            'frontend_collapse_heading_text_color',
            'frontend_product_title_color',
            'frontend_product_price_color',
            'frontend_product_description_color',
            'frontend_choose_button_color',
            'frontend_choose_button_text_color',
            'frontend_view_cart_button_color',
            'frontend_view_cart_button_text_color',
            'frontend_checkout_button_color',
            'frontend_checkout_button_text_color'
        ];
        layoutInputs.forEach((name) => {
            const color = document.querySelector(`input[name="${name}"]`);
            const text = document.querySelector(`input[name="${name}_text"]`);
            if (color) color.addEventListener('input', updateLayoutPreview);
            if (text) text.addEventListener('input', updateLayoutPreview);
        });
        extraLayoutInputs.forEach((name) => {
            const color = document.querySelector(`input[name="${name}"]`);
            const text = document.querySelector(`input[name="${name}_text"]`);
            if (color) color.addEventListener('input', updateLayoutPreview);
            if (text) text.addEventListener('input', updateLayoutPreview);
        });
    })();



    (function() {
        const tabs = document.querySelectorAll('.settings-tabs a[href^="#"]');
        if (!tabs.length) {
            return;
        }
        const setActive = (targetId) => {
            tabs.forEach((tab) => {
                const isActive = tab.getAttribute('href') === targetId;
                tab.classList.toggle('active', isActive);
            });
        };

        if (window.location.hash) {
            setActive(window.location.hash);
        } else {
            setActive(tabs[0].getAttribute('href'));
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                const targetId = tab.getAttribute('href');
                const target = document.querySelector(targetId);
                if (!target) {
                    return;
                }
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', targetId);
                setActive(targetId);
            });
        });
    })();

    (function() {
        const editor = document.getElementById('pickupScheduleEditor');
        const hiddenInput = document.getElementById('pickupScheduleJson');
        if (!editor || !hiddenInput) {
            return;
        }

        const syncSchedule = () => {
            const schedule = {};
            editor.querySelectorAll('[data-pickup-day]').forEach((card) => {
                const day = card.getAttribute('data-pickup-day');
                const holiday = card.querySelector('.pickup-holiday-checkbox')?.checked || false;
                const times = Array.from(card.querySelectorAll('.pickup-time-input'))
                    .map((input) => input.value)
                    .filter((value) => value);

                card.classList.toggle('is-holiday', holiday);
                const holidayBadge = card.querySelector('.pickup-holiday-badge');
                if (holidayBadge) {
                    holidayBadge.classList.toggle('d-none', !holiday);
                }

                schedule[day] = {
                    holiday,
                    times: holiday ? [] : times,
                };
            });

            hiddenInput.value = JSON.stringify(schedule);
        };

        const createSlotRow = () => {
            const row = document.createElement('div');
            row.className = 'pickup-slot-row';
            row.innerHTML = `
                <input type="time" class="form-control pickup-time-input" value="">
                <button type="button" class="btn btn-outline-danger btn-sm pickup-remove-slot">
                    <i class="fa fa-trash"></i>
                </button>
            `;
            return row;
        };

        editor.addEventListener('click', (event) => {
            const addBtn = event.target.closest('.pickup-add-slot');
            if (addBtn) {
                const card = addBtn.closest('[data-pickup-day]');
                card.querySelector('.pickup-slot-list')?.appendChild(createSlotRow());
                syncSchedule();
                return;
            }

            const removeBtn = event.target.closest('.pickup-remove-slot');
            if (removeBtn) {
                const list = removeBtn.closest('.pickup-slot-list');
                const rows = list ? list.querySelectorAll('.pickup-slot-row') : [];
                if (rows.length > 1) {
                    removeBtn.closest('.pickup-slot-row')?.remove();
                } else if (rows[0]) {
                    rows[0].querySelector('.pickup-time-input').value = '';
                }
                syncSchedule();
            }
        });

        editor.addEventListener('input', (event) => {
            if (event.target.classList.contains('pickup-time-input')) {
                syncSchedule();
            }
        });

        editor.addEventListener('change', (event) => {
            if (event.target.classList.contains('pickup-holiday-checkbox')) {
                syncSchedule();
            }
        });

        syncSchedule();
    })();

    (function() {
        const editor = document.getElementById('deliveryScheduleEditor');
        const hiddenInput = document.getElementById('deliveryScheduleJson');
        if (!editor || !hiddenInput) {
            return;
        }

        const syncSchedule = () => {
            const schedule = {};
            editor.querySelectorAll('[data-delivery-day]').forEach((card) => {
                const day = card.getAttribute('data-delivery-day');
                const holiday = card.querySelector('.delivery-holiday-checkbox')?.checked || false;
                const times = Array.from(card.querySelectorAll('.delivery-time-input'))
                    .map((input) => input.value)
                    .filter((value) => value);

                card.classList.toggle('is-holiday', holiday);
                const holidayBadge = card.querySelector('.delivery-holiday-badge');
                if (holidayBadge) {
                    holidayBadge.classList.toggle('d-none', !holiday);
                }

                schedule[day] = {
                    holiday,
                    times: holiday ? [] : times,
                };
            });

            hiddenInput.value = JSON.stringify(schedule);
        };

        const createSlotRow = () => {
            const row = document.createElement('div');
            row.className = 'delivery-slot-row';
            row.innerHTML = `
                <input type="time" class="form-control delivery-time-input" value="">
                <button type="button" class="btn btn-outline-danger btn-sm delivery-remove-slot">
                    <i class="fa fa-trash"></i>
                </button>
            `;
            return row;
        };

        editor.addEventListener('click', (event) => {
            const addBtn = event.target.closest('.delivery-add-slot');
            if (addBtn) {
                const card = addBtn.closest('[data-delivery-day]');
                card.querySelector('.delivery-slot-list')?.appendChild(createSlotRow());
                syncSchedule();
                return;
            }

            const removeBtn = event.target.closest('.delivery-remove-slot');
            if (removeBtn) {
                const list = removeBtn.closest('.delivery-slot-list');
                const rows = list ? list.querySelectorAll('.delivery-slot-row') : [];
                if (rows.length > 1) {
                    removeBtn.closest('.delivery-slot-row')?.remove();
                } else if (rows[0]) {
                    rows[0].querySelector('.delivery-time-input').value = '';
                }
                syncSchedule();
            }
        });

        editor.addEventListener('input', (event) => {
            if (event.target.classList.contains('delivery-time-input')) {
                syncSchedule();
            }
        });

        editor.addEventListener('change', (event) => {
            if (event.target.classList.contains('delivery-holiday-checkbox')) {
                syncSchedule();
            }
        });

        syncSchedule();
    })();

    (function() {
        const editor = document.getElementById('openingHoursEditor');
        const hiddenInput = document.getElementById('openingHoursJson');
        if (!editor || !hiddenInput) {
            return;
        }

        const syncSchedule = () => {
            const schedule = {};
            editor.querySelectorAll('[data-opening-hours-day]').forEach((card) => {
                const day = card.getAttribute('data-opening-hours-day');
                const holiday = card.querySelector('.opening-hours-holiday-checkbox')?.checked || false;
                const times = Array.from(card.querySelectorAll('.opening-hours-time-input'))
                    .map((input) => input.value)
                    .filter((value) => value);

                card.classList.toggle('is-holiday', holiday);
                const holidayBadge = card.querySelector('.opening-hours-holiday-badge');
                if (holidayBadge) {
                    holidayBadge.classList.toggle('d-none', !holiday);
                }

                schedule[day] = {
                    holiday,
                    times: holiday ? [] : times,
                };
            });

            hiddenInput.value = JSON.stringify(schedule);
        };

        const createSlotRow = () => {
            const row = document.createElement('div');
            row.className = 'opening-hours-slot-row';
            row.innerHTML = `
                <input type="time" class="form-control opening-hours-time-input" value="">
                <button type="button" class="btn btn-outline-danger btn-sm opening-hours-remove-slot">
                    <i class="fa fa-trash"></i>
                </button>
            `;
            return row;
        };

        editor.addEventListener('click', (event) => {
            const addBtn = event.target.closest('.opening-hours-add-slot');
            if (addBtn) {
                const card = addBtn.closest('[data-opening-hours-day]');
                card.querySelector('.opening-hours-slot-list')?.appendChild(createSlotRow());
                syncSchedule();
                return;
            }

            const removeBtn = event.target.closest('.opening-hours-remove-slot');
            if (removeBtn) {
                const list = removeBtn.closest('.opening-hours-slot-list');
                const rows = list ? list.querySelectorAll('.opening-hours-slot-row') : [];
                if (rows.length > 1) {
                    removeBtn.closest('.opening-hours-slot-row')?.remove();
                } else if (rows[0]) {
                    rows[0].querySelector('.opening-hours-time-input').value = '';
                }
                syncSchedule();
            }
        });

        editor.addEventListener('input', (event) => {
            if (event.target.classList.contains('opening-hours-time-input')) {
                syncSchedule();
            }
        });

        editor.addEventListener('change', (event) => {
            if (event.target.classList.contains('opening-hours-holiday-checkbox')) {
                syncSchedule();
            }
        });

        syncSchedule();
    })();

    (function() {
        const editor = document.getElementById('paymentMethodsEditor');
        const hiddenInput = document.getElementById('paymentMethodsJson');
        const addButton = document.getElementById('addPaymentMethodBtn');
        const emptyState = document.getElementById('paymentMethodsEmpty');
        if (!editor || !hiddenInput) {
            return;
        }

        const i18n = {
            noteDynamic: @json(__('payment_method_note_dynamic')),
            titlePlaceholder: @json(__('payment_method_placeholder')),
            codePlaceholder: @json(__('payment_method_code_placeholder')),
            descriptionPlaceholder: @json(__('payment_method_description_placeholder')),
            instructionsPlaceholder: @json(__('payment_method_instructions_placeholder')),
            active: @json(__('payment_method_active')),
            title: @json(__('payment_method_title')),
            code: @json(__('payment_method_code')),
            type: @json(__('payment_method_type')),
            statusLabel: @json(__('payment_method_status_label')),
            description: @json(__('payment_method_description')),
            environment: @json(__('payment_method_environment')),
            stripe: @json(__('payment_method_stripe')),
            cashOnDelivery: @json(__('payment_method_cash_on_delivery')),
            bankAccount: @json(__('payment_method_bank_account')),
            custom: @json(__('payment_method_custom')),
            sandbox: @json(__('payment_method_sandbox')),
            live: @json(__('payment_method_live')),
            stripeNote: @json(__('payment_method_stripe_note')),
            codNote: @json(__('payment_method_cod_note')),
            livePublicKey: @json(__('payment_method_live_public_key')),
            liveSecretKey: @json(__('payment_method_live_secret_key')),
            sandboxPublicKey: @json(__('payment_method_sandbox_public_key')),
            sandboxSecretKey: @json(__('payment_method_sandbox_secret_key')),
            livePrivateKey: @json(__('payment_method_live_private_key')),
            sandboxPrivateKey: @json(__('payment_method_sandbox_private_key')),
            accountTitle: @json(__('payment_method_account_title')),
            iban: @json(__('payment_method_iban')),
            branchName: @json(__('payment_method_branch_name')),
            accountNumber: @json(__('payment_method_account_number')),
            instructions: @json(__('payment_method_instructions')),
            emptyState: @json(__('payment_method_empty_state')),
            customNote: @json(__('payment_method_custom_note')),
        };

        const slugify = (value) => String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');

        const methodTypeLabel = (type) => {
            const labels = {
                stripe: i18n.stripe,
                cash_on_delivery: i18n.cashOnDelivery,
                bank_account: i18n.bankAccount,
                custom: i18n.custom,
            };

            return labels[type] || i18n.custom;
        };

        const emptyConfig = (type) => {
            if (type === 'stripe') {
                return {
                    environment: 'sandbox',
                    live_public_key: '',
                    live_secret_key: '',
                    sandbox_public_key: '',
                    sandbox_secret_key: '',
                };
            }

            if (type === 'bank_account') {
                return {
                    account_title: '',
                    iban: '',
                    branch_name: '',
                    account_number: '',
                };
            }

            if (type === 'custom') {
                return {
                    environment: 'sandbox',
                    live_public_key: '',
                    live_private_key: '',
                    sandbox_public_key: '',
                    sandbox_private_key: '',
                };
            }

            return {};
        };

        const createMethodCard = (method = {}) => {
            const type = method.type || 'custom';
            const config = method.config || emptyConfig(type);
            const card = document.createElement('div');
            card.className = 'col-xl-6 payment-method-item';
            card.dataset.paymentMethodId = method.id || `pm_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
            card.innerHTML = `
                <div class="payment-method-card" data-payment-method>
                    <div class="payment-method-head">
                        <div>
                            <h6 class="payment-method-title">${method.title || i18n.title}</h6>
                            <div class="payment-method-subtitle">${i18n.noteDynamic}</div>
                        </div>
                        <div class="payment-method-actions">
                            <label class="payment-method-toggle">
                                <input type="checkbox" class="form-check-input mt-0 payment-method-active" ${method.is_active ? 'checked' : ''}>
                                <span>${i18n.active}</span>
                            </label>
                            <button type="button" class="btn btn-outline-danger btn-sm payment-method-remove">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="payment-method-grid">
                        <div>
                            <label class="form-label fw-bold">${i18n.title}</label>
                            <input type="text" class="form-control payment-method-title-input" value="${method.title || ''}" placeholder="${i18n.titlePlaceholder}">
                        </div>
                        <div>
                            <label class="form-label fw-bold">${i18n.code}</label>
                            <input type="text" class="form-control payment-method-code-input" value="${method.code || ''}" placeholder="${i18n.codePlaceholder}">
                        </div>
                        <div>
                            <label class="form-label fw-bold">${i18n.type}</label>
                            <select class="form-select payment-method-type">
                                <option value="stripe" ${type === 'stripe' ? 'selected' : ''}>${i18n.stripe}</option>
                                <option value="cash_on_delivery" ${type === 'cash_on_delivery' ? 'selected' : ''}>${i18n.cashOnDelivery}</option>
                                <option value="bank_account" ${type === 'bank_account' ? 'selected' : ''}>${i18n.bankAccount}</option>
                                <option value="custom" ${type === 'custom' ? 'selected' : ''}>${i18n.custom}</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-bold">${i18n.statusLabel}</label>
                            <input type="text" class="form-control payment-method-type-label" value="${methodTypeLabel(type)}" readonly>
                        </div>
                        <div class="full-span">
                            <label class="form-label fw-bold">${i18n.description}</label>
                            <textarea class="form-control payment-method-description-input" rows="2" placeholder="${i18n.descriptionPlaceholder}">${method.description || ''}</textarea>
                        </div>
                    </div>
                    <div class="payment-method-config">
                        <div class="payment-method-fields payment-method-fields-stripe ${type === 'stripe' ? '' : 'd-none'}">
                            <div class="payment-method-grid">
                                <div>
                                    <label class="form-label fw-bold">${i18n.environment}</label>
                                    <select class="form-select payment-method-environment">
                                        <option value="sandbox" ${(config.environment || 'sandbox') === 'sandbox' ? 'selected' : ''}>${i18n.sandbox}</option>
                                        <option value="live" ${(config.environment || '') === 'live' ? 'selected' : ''}>${i18n.live}</option>
                                    </select>
                                </div>
                                <div class="payment-method-config-note">${i18n.stripeNote}</div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.livePublicKey}</label>
                                    <input type="text" class="form-control payment-method-live-public-key" value="${config.live_public_key || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.liveSecretKey}</label>
                                    <input type="text" class="form-control payment-method-live-secret-key" value="${config.live_secret_key || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.sandboxPublicKey}</label>
                                    <input type="text" class="form-control payment-method-sandbox-public-key" value="${config.sandbox_public_key || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.sandboxSecretKey}</label>
                                    <input type="text" class="form-control payment-method-sandbox-secret-key" value="${config.sandbox_secret_key || ''}">
                                </div>
                            </div>
                        </div>
                        <div class="payment-method-fields payment-method-fields-cash_on_delivery ${type === 'cash_on_delivery' ? '' : 'd-none'}">
                            <div class="payment-method-config-note">${i18n.codNote}</div>
                        </div>
                        <div class="payment-method-fields payment-method-fields-bank_account ${type === 'bank_account' ? '' : 'd-none'}">
                            <div class="payment-method-grid">
                                <div>
                                    <label class="form-label fw-bold">${i18n.accountTitle}</label>
                                    <input type="text" class="form-control payment-method-account-title" value="${config.account_title || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.iban}</label>
                                    <input type="text" class="form-control payment-method-iban" value="${config.iban || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.branchName}</label>
                                    <input type="text" class="form-control payment-method-branch-name" value="${config.branch_name || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.accountNumber}</label>
                                    <input type="text" class="form-control payment-method-account-number" value="${config.account_number || ''}">
                                </div>
                            </div>
                        </div>
                        <div class="payment-method-fields payment-method-fields-custom ${type === 'custom' ? '' : 'd-none'}">
                            <div class="payment-method-grid">
                                <div>
                                    <label class="form-label fw-bold">${i18n.environment}</label>
                                    <select class="form-select payment-method-custom-environment">
                                        <option value="sandbox" ${(config.environment || 'sandbox') === 'sandbox' ? 'selected' : ''}>${i18n.sandbox}</option>
                                        <option value="live" ${(config.environment || '') === 'live' ? 'selected' : ''}>${i18n.live}</option>
                                    </select>
                                </div>
                                <div class="payment-method-config-note">${i18n.customNote}</div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.livePublicKey}</label>
                                    <input type="text" class="form-control payment-method-custom-live-public-key" value="${config.live_public_key || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.livePrivateKey}</label>
                                    <input type="text" class="form-control payment-method-custom-live-private-key" value="${config.live_private_key || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.sandboxPublicKey}</label>
                                    <input type="text" class="form-control payment-method-custom-sandbox-public-key" value="${config.sandbox_public_key || ''}">
                                </div>
                                <div>
                                    <label class="form-label fw-bold">${i18n.sandboxPrivateKey}</label>
                                    <input type="text" class="form-control payment-method-custom-sandbox-private-key" value="${config.sandbox_private_key || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            const innerCard = card.querySelector('[data-payment-method]');
            if (innerCard) {
                innerCard.dataset.paymentMethodId = card.dataset.paymentMethodId;
            }

            return card;
        };

        const updateMethodTypeState = (card) => {
            const type = card.querySelector('.payment-method-type')?.value || 'custom';
            card.querySelector('.payment-method-type-label').value = methodTypeLabel(type);
            card.querySelectorAll('.payment-method-fields').forEach((section) => {
                const shouldShow = section.classList.contains(`payment-method-fields-${type}`);
                section.classList.toggle('d-none', !shouldShow);
            });
        };

        const syncMethods = () => {
            const methods = [];
            editor.querySelectorAll('[data-payment-method]').forEach((card, index) => {
                const titleInput = card.querySelector('.payment-method-title-input');
                const codeInput = card.querySelector('.payment-method-code-input');
                const type = card.querySelector('.payment-method-type')?.value || 'custom';
                const title = titleInput?.value?.trim() || '';
                const code = slugify(codeInput?.value || title || `payment_method_${index + 1}`);
                const description = card.querySelector('.payment-method-description-input')?.value?.trim() || '';
                const method = {
                    id: card.dataset.paymentMethodId || `pm_${Date.now()}_${index}`,
                    title: title || methodTypeLabel(type),
                    code,
                    type,
                    description,
                    is_active: card.querySelector('.payment-method-active')?.checked || false,
                    config: emptyConfig(type),
                };

                if (type === 'stripe') {
                    method.config = {
                        environment: card.querySelector('.payment-method-environment')?.value === 'live' ? 'live' : 'sandbox',
                        live_public_key: card.querySelector('.payment-method-live-public-key')?.value?.trim() || '',
                        live_secret_key: card.querySelector('.payment-method-live-secret-key')?.value?.trim() || '',
                        sandbox_public_key: card.querySelector('.payment-method-sandbox-public-key')?.value?.trim() || '',
                        sandbox_secret_key: card.querySelector('.payment-method-sandbox-secret-key')?.value?.trim() || '',
                    };
                } else if (type === 'bank_account') {
                    method.config = {
                        account_title: card.querySelector('.payment-method-account-title')?.value?.trim() || '',
                        iban: card.querySelector('.payment-method-iban')?.value?.trim() || '',
                        branch_name: card.querySelector('.payment-method-branch-name')?.value?.trim() || '',
                        account_number: card.querySelector('.payment-method-account-number')?.value?.trim() || '',
                    };
                } else if (type === 'custom') {
                    method.config = {
                        environment: card.querySelector('.payment-method-custom-environment')?.value === 'live' ? 'live' : 'sandbox',
                        live_public_key: card.querySelector('.payment-method-custom-live-public-key')?.value?.trim() || '',
                        live_private_key: card.querySelector('.payment-method-custom-live-private-key')?.value?.trim() || '',
                        sandbox_public_key: card.querySelector('.payment-method-custom-sandbox-public-key')?.value?.trim() || '',
                        sandbox_private_key: card.querySelector('.payment-method-custom-sandbox-private-key')?.value?.trim() || '',
                    };
                }

                card.querySelector('.payment-method-title').textContent = method.title;
                codeInput.value = code;
                methods.push(method);
            });

            hiddenInput.value = JSON.stringify(methods);
            if (emptyState) {
                emptyState.classList.toggle('d-none', methods.length > 0);
            }
        };

        editor.querySelectorAll('.payment-method-item').forEach((item, index) => {
            if (!item.dataset.paymentMethodId) {
                item.dataset.paymentMethodId = `pm_existing_${index + 1}`;
            }
            const innerCard = item.querySelector('[data-payment-method]');
            if (innerCard && !innerCard.dataset.paymentMethodId) {
                innerCard.dataset.paymentMethodId = item.dataset.paymentMethodId;
            }
        });
        editor.querySelectorAll('[data-payment-method]').forEach((card) => {
            updateMethodTypeState(card);
        });

        if (addButton) {
            addButton.addEventListener('click', () => {
                const card = createMethodCard({
                    type: 'custom',
                    title: '',
                    code: '',
                    description: '',
                    is_active: false,
                    config: emptyConfig('custom'),
                });
                editor.appendChild(card);
                syncMethods();
            });
        }

        editor.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.payment-method-remove');
            if (!removeButton) {
                return;
            }

            removeButton.closest('.payment-method-item')?.remove();
            syncMethods();
        });

        editor.addEventListener('change', (event) => {
            if (event.target.classList.contains('payment-method-type')) {
                const card = event.target.closest('[data-payment-method]');
                if (card) {
                    updateMethodTypeState(card);
                }
            }

            syncMethods();
        });

        editor.addEventListener('input', (event) => {
            const card = event.target.closest('[data-payment-method]');
            if (!card) {
                return;
            }

            if (event.target.classList.contains('payment-method-title-input')) {
                const codeInput = card.querySelector('.payment-method-code-input');
                if (codeInput && !codeInput.value.trim()) {
                    codeInput.value = slugify(event.target.value);
                }
            }

            syncMethods();
        });

        syncMethods();
    })();
    </script>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                const $tz = $('.select2-timezone');
                if ($tz.length) {
                    $tz.select2({
                        width: '100%',
                        minimumResultsForSearch: 0,
                    });
                }

                const providerField = document.getElementById('map_provider');
                const googleWrap = document.getElementById('googleMapsKeyWrap');

                const syncMapProviderFields = () => {
                    if (!providerField || !googleWrap) {
                        return;
                    }

                    googleWrap.style.display = providerField.value === 'google' ? '' : 'none';
                };

                if (providerField) {
                    providerField.addEventListener('change', syncMapProviderFields);
                    syncMapProviderFields();
                }
            });
        </script>
    @endpush
@endsection
