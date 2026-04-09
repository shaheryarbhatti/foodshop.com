@extends('layouts.frontend')

@php
    $activeNav = 'home';
    $currencyRate = max((float) ($currentCurrency?->exchange_rate ?? 1), 1);
    $currencySymbol = $currentCurrency?->symbol ?: '$';
    $frontendCustomerHasAddress = $frontendCustomer
        && filled($frontendCustomer->address)
        && filled($frontendCustomer->city)
        && filled($frontendCustomer->postal_code)
        && filled($frontendCustomer->country_id);
    $frontendCustomerData = $frontendCustomer ? [
        'id' => $frontendCustomer->id,
        'name' => $frontendCustomer->full_name,
        'email' => $frontendCustomer->email,
        'has_address' => $frontendCustomerHasAddress,
    ] : null;
    $fallbackImage = asset('public/assets/images/login/login_image.jpg');
    $frontendThemeSettings = \App\Models\Setting::getMany([
        'frontend_collapse_bg_color' => '#d90f02',
        'frontend_collapse_heading_text_color' => '#ffffff',
        'frontend_product_title_color' => '#eb3826',
        'frontend_product_price_color' => '#ff1200',
        'frontend_product_description_color' => '#333333',
        'frontend_choose_button_color' => '#ffca27',
        'frontend_choose_button_text_color' => '#111111',
        'frontend_view_cart_button_color' => '#ffc933',
        'frontend_view_cart_button_text_color' => '#161616',
        'frontend_checkout_button_color' => '#151515',
        'frontend_checkout_button_text_color' => '#ffffff',
        'theme_primary' => '#7367f0',
    ]);
    $themePrimaryColor = $frontendThemeSettings['theme_primary'];
    $frontendCollapseBgColor = $frontendThemeSettings['frontend_collapse_bg_color'];
    $frontendCollapseHeadingTextColor = $frontendThemeSettings['frontend_collapse_heading_text_color'];
    $frontendProductTitleColor = $frontendThemeSettings['frontend_product_title_color'];
    $frontendProductPriceColor = $frontendThemeSettings['frontend_product_price_color'];
    $frontendProductDescriptionColor = $frontendThemeSettings['frontend_product_description_color'];
    $frontendChooseButtonColor = $frontendThemeSettings['frontend_choose_button_color'];
    $frontendChooseButtonTextColor = $frontendThemeSettings['frontend_choose_button_text_color'];
    $frontendViewCartButtonColor = $frontendThemeSettings['frontend_view_cart_button_color'];
    $frontendViewCartButtonTextColor = $frontendThemeSettings['frontend_view_cart_button_text_color'];
    $frontendCheckoutButtonColor = $frontendThemeSettings['frontend_checkout_button_color'];
    $frontendCheckoutButtonTextColor = $frontendThemeSettings['frontend_checkout_button_text_color'];
    $menuProducts = $categories->flatMap(function ($category) use ($fallbackImage) {
        return $category->products->map(function ($product) use ($category, $fallbackImage) {
            return [
                'id' => $product->id,
                'category_id' => $category->id,
                'serial_number' => $product->serial_number,
                'title' => $product->title,
                'description' => $product->description,
                'image_url' => $product->image ? asset('public/' . $product->image) : $fallbackImage,
                'base_price' => (float) $product->base_price,
                'addons' => $product->addons->map(fn ($addon) => [
                    'id' => $addon->id,
                    'title' => $addon->title,
                    'selection_type' => $addon->selection_type,
                    'values' => $addon->values->map(fn ($value) => [
                        'id' => $value->id,
                        'title' => $value->title,
                        'price' => (float) ($value->price ?? 0),
                    ])->values()->all(),
                ])->values()->all(),
            ];
        });
    })->values();
@endphp

@section('title', __('frontend_homepage'))

@section('styles')
<link rel="stylesheet" href="{{ asset('public/assets/css/vendors/select2.css') }}">
@if($mapProvider === 'leaflet')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endif
<style>
:root{--theme-primary:{{ $themePrimaryColor }};--frontend-collapse-bg:{{ $frontendCollapseBgColor }};--frontend-collapse-heading-text:{{ $frontendCollapseHeadingTextColor }};--frontend-product-title:{{ $frontendProductTitleColor }};--frontend-product-price:{{ $frontendProductPriceColor }};--frontend-product-description:{{ $frontendProductDescriptionColor }};--frontend-choose-btn-bg:{{ $frontendChooseButtonColor }};--frontend-choose-btn-text:{{ $frontendChooseButtonTextColor }};--frontend-view-cart-btn-bg:{{ $frontendViewCartButtonColor }};--frontend-view-cart-btn-text:{{ $frontendViewCartButtonTextColor }};--frontend-checkout-btn-bg:{{ $frontendCheckoutButtonColor }};--frontend-checkout-btn-text:{{ $frontendCheckoutButtonTextColor }}}.menu-page{padding:26px 0 42px}.menu-shell{padding:14px;border-radius:30px;background:rgba(120,120,120,.60);box-shadow:0 24px 64px rgba(0,0,0,.28);backdrop-filter:blur(6px)}.menu-grid{display:grid;grid-template-columns:minmax(0,1.95fr) minmax(320px,.95fr);gap:14px;align-items:start}.catalog-stack{display:flex;flex-direction:column;gap:14px}.catalog-section,.basket-card,.product-card,.menu-modal .modal-content,.address-modal .modal-content{background:rgba(255,255,255,.96);box-shadow:0 10px 22px rgba(15,23,42,.12)}.catalog-section{border-radius:18px;overflow:hidden}.catalog-heading{width:100%;border:0;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 22px;background:var(--frontend-collapse-bg);color:var(--frontend-collapse-heading-text);font-size:1.08rem;font-weight:900;text-align:left}.catalog-heading i{width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,.18)}.catalog-panel{padding:18px 18px 10px}.product-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 18px}.product-card{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:12px;align-items:center;padding:16px 12px;border-radius:16px;border:1px solid rgba(15,23,42,.08);transition:transform .18s ease,box-shadow .18s ease}.product-card:hover{transform:translateY(-2px);box-shadow:0 16px 28px rgba(15,23,42,.10)}.product-title{color:var(--frontend-product-title);font-size:1rem;font-weight:800;line-height:1.25;margin-bottom:6px}.product-copy{color:var(--frontend-product-description);font-size:.92rem;line-height:1.45}.product-copy p{display:inline;margin:0}.product-price{color:var(--frontend-product-price);font-size:1rem;font-weight:900;white-space:nowrap}.choose-btn,.basket-cta,.modal-submit-btn,.address-submit-btn{border:0;border-radius:12px;background:linear-gradient(135deg,var(--frontend-choose-btn-bg),var(--frontend-choose-btn-bg));color:var(--frontend-choose-btn-text);font-weight:900}.choose-btn{min-width:96px;padding:12px 16px}.basket-card{padding:28px 28px 24px;border-radius:18px;box-shadow:0 18px 34px rgba(15,23,42,.16)}.basket-title{margin:0 0 8px;font-size:clamp(2rem,3vw,2.4rem);font-weight:400;text-align:center}.basket-note{margin:0 0 18px;text-align:center;color:#425466;font-size:1.02rem}.basket-note strong{color:#213547}.basket-cta{display:inline-flex;align-items:center;justify-content:center;width:100%;min-height:48px;text-decoration:none;margin-bottom:24px}.basket-status{display:none;margin-bottom:20px;padding:12px 14px;border-radius:14px;background:#edfdf2;color:#14532d;font-weight:700}.basket-status.is-visible{display:block}.basket-item{display:grid;grid-template-columns:auto 1fr;gap:14px;align-items:start;padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid rgba(15,23,42,.12)}.basket-remove{width:40px;height:30px;border:0;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:#de3141;color:#fff}.basket-item-title{font-size:1rem;font-weight:800;color:#182433}.basket-meta{margin-top:4px;color:#52606d;font-size:.92rem;line-height:1.55}.basket-empty{padding:18px 16px;border:1px dashed rgba(15,23,42,.18);border-radius:14px;color:#52606d;text-align:center;background:rgba(248,250,252,.92)}.basket-summary-line{display:flex;justify-content:space-between;gap:12px;margin-bottom:10px;color:#1f2937}.basket-subtotal{padding-top:14px;margin-top:10px;border-top:1px solid rgba(15,23,42,.12);font-size:1.05rem;font-weight:900}.basket-links{margin-top:18px;color:#334155;line-height:1.7}.basket-links a{color:#1456ff}.basket-actions{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:22px}.basket-actions a{display:flex;align-items:center;justify-content:center;min-height:54px;padding:0 18px;border-radius:16px;font-size:1rem;font-weight:900;line-height:1.1;text-align:center;text-decoration:none!important;white-space:nowrap;overflow:hidden;box-shadow:0 10px 22px rgba(15,23,42,.12);transition:transform .18s ease,box-shadow .18s ease}.basket-actions a:hover{transform:translateY(-1px);box-shadow:0 14px 28px rgba(15,23,42,.16)}.basket-actions .secondary{background:linear-gradient(135deg,var(--frontend-view-cart-btn-bg),var(--frontend-view-cart-btn-bg));color:var(--frontend-view-cart-btn-text)}.basket-actions .primary{background:linear-gradient(135deg,var(--frontend-checkout-btn-bg),var(--frontend-checkout-btn-bg));color:var(--frontend-checkout-btn-text)}.basket-actions a span{display:block;max-width:100%;overflow:hidden;text-overflow:ellipsis}.menu-modal .modal-content,.address-modal .modal-content{border:0;border-radius:22px;overflow:hidden;box-shadow:0 28px 60px rgba(0,0,0,.32)}.menu-modal .modal-header,.address-modal .modal-header{padding:20px 22px;background:linear-gradient(135deg,#ef4a3a,#e64134);color:#fff;border:0}.menu-modal .modal-title,.address-modal .modal-title{font-size:1.15rem;font-weight:900}.menu-modal .btn-close,.address-modal .btn-close{filter:invert(1);opacity:1}.menu-modal .modal-body,.address-modal .modal-body{padding:20px}.modal-product-image{width:100%;height:260px;object-fit:cover;border-radius:16px;margin-bottom:18px}.modal-product-summary{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px;align-items:start;margin-bottom:22px}.modal-product-copy{color:#2f3640;font-size:1rem;line-height:1.55}.modal-price-pill{display:inline-flex;align-items:center;justify-content:center;min-width:100px;min-height:54px;border-radius:12px;background:#de3141;color:#fff;font-size:1.1rem;font-weight:900;padding:0 18px}.addon-block{margin-bottom:18px}.addon-title,.address-form-label{display:block;margin-bottom:10px;color:#2b3139;font-size:.98rem;font-weight:900}.addon-select,.addon-remarks,.address-form-control,.address-search-box{border-radius:12px;border-color:rgba(15,23,42,.16);min-height:52px;font-size:1rem}.addon-select[multiple]{min-height:124px}.addon-help,.address-help{margin-top:8px;color:#667085;font-size:.84rem}.select2-container{width:100%!important}.select2-container--default .select2-selection--multiple{min-height:52px;border-radius:12px;border-color:rgba(15,23,42,.16);padding:8px 10px}.select2-container--default .select2-selection--single{height:52px;border-radius:12px;border-color:rgba(15,23,42,.16);padding:11px 12px}.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:28px;padding-left:0}.select2-container--default .select2-selection--single .select2-selection__arrow{height:50px}.select2-container--default .select2-selection--multiple .select2-selection__choice{background:#fff3c4;border:1px solid #ffd86a;color:#1f2937;border-radius:999px;padding:3px 10px}.select2-container--default .select2-results__option--selected{background:#fff3c4;color:#1f2937}.select2-dropdown{border-color:rgba(15,23,42,.16);border-radius:12px;overflow:hidden}.modal-qty-row{display:flex;align-items:center;gap:10px;margin:18px 0 20px}.qty-btn,.qty-display{width:52px;height:48px;border-radius:10px;border:1px solid rgba(15,23,42,.20);background:#fff;color:#1f2937;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem}.qty-btn{cursor:pointer}.modal-submit-btn,.address-submit-btn{width:100%;min-height:52px;font-size:1.1rem}.address-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.address-full{grid-column:1/-1}.company-name-wrap{display:none}.address-search-wrap{position:relative}.address-suggestions{display:none;max-height:240px;overflow:auto;margin-top:10px;padding:0;border:1px solid rgba(15,23,42,.12);border-radius:14px;background:#fff;box-shadow:0 18px 34px rgba(15,23,42,.10)}.address-suggestions.is-visible{display:block}.address-suggestion{padding:14px 16px;border-bottom:1px solid rgba(15,23,42,.08);background:#fff;cursor:pointer}.address-suggestion:last-child{border-bottom:0}.address-suggestion-title{display:block;color:#111827;font-size:1rem;font-weight:800;line-height:1.3}.address-suggestion-copy{display:block;margin-top:6px;color:#111827;font-size:.92rem;line-height:1.45;word-break:break-word}.address-feedback{display:none;margin-top:12px;padding:12px 14px;border-radius:12px;font-size:.9rem;font-weight:700}.address-feedback.is-visible{display:block}.address-feedback.is-error{background:#fff1f2;color:#be123c}.address-feedback.is-success{background:#edfdf2;color:#166534}@media (max-width:1199.98px){.product-grid{grid-template-columns:1fr}}@media (max-width:991.98px){.menu-grid{grid-template-columns:1fr}.basket-card{position:static}}@media (max-width:767.98px){.menu-page{padding-top:16px}.menu-shell{padding:10px;border-radius:20px}.catalog-heading{padding:16px;font-size:1rem}.product-card{grid-template-columns:1fr}.choose-btn{width:100%}.basket-card{padding:22px 18px}.basket-actions{grid-template-columns:1fr}.basket-actions a{white-space:normal;padding:14px 18px}.basket-actions a span{overflow:visible;text-overflow:clip}.modal-product-summary{grid-template-columns:1fr}.modal-product-image{height:210px}.address-grid{grid-template-columns:1fr}}
    </style>
    <style>
        .basket-min-order-note {
            margin-top: 12px;
            border-radius: 16px;
            padding: 10px 16px;
            font-weight: 700;
            text-align: center;
            background: rgba(249, 115, 22, 0.08);
            color: #c2410c;
            border: 1px solid rgba(249, 115, 22, 0.45);
        }
    </style>
    <style>
.address-map-card{position:relative;margin-top:4px;border:1px solid rgba(15,23,42,.12);border-radius:18px;background:#fff;overflow:hidden;box-shadow:0 14px 28px rgba(15,23,42,.08)}.address-map-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:#f8fafc;border-bottom:1px solid rgba(15,23,42,.08)}.address-map-head span{font-size:.96rem;font-weight:800;color:#1f2937}.address-map-head small{font-size:.8rem;color:#64748b}.address-map-canvas{height:280px;width:100%;background:linear-gradient(180deg,#eff6ff,#f8fafc)}.address-map-loader{position:absolute;inset:auto 16px 16px auto;display:none;align-items:center;gap:10px;padding:10px 14px;border-radius:999px;background:rgba(15,23,42,.88);color:#fff;font-size:.84rem;font-weight:700;box-shadow:0 10px 22px rgba(15,23,42,.24);z-index:1000}.address-map-loader.is-visible{display:inline-flex}.address-map-loader i{font-size:.95rem}.gm-style .gm-style-iw-c{padding:12px!important;border-radius:14px!important}.leaflet-control-attribution{font-size:10px}.leaflet-popup-content-wrapper{border-radius:14px}.pac-container{z-index:20000!important;background:#fff!important;border:1px solid rgba(15,23,42,.12)!important;border-radius:14px!important;box-shadow:0 18px 34px rgba(15,23,42,.12)!important;overflow:hidden}.pac-container .pac-item{padding:10px 14px;color:#111827;background:#fff}.pac-container .pac-item:hover{background:#f8fafc}.pac-container .pac-item-query{color:#111827;font-size:.95rem;font-weight:700}.address-login-note{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 0 18px;padding:14px 16px;border-radius:16px;background:linear-gradient(135deg,rgba(255,202,39,.16),rgba(255,255,255,.98));border:1px solid rgba(255,202,39,.38);box-shadow:0 12px 22px rgba(15,23,42,.06)}.address-login-copy{color:#334155;font-size:.95rem;line-height:1.55;font-weight:600;flex:1}.address-login-copy strong{display:block;margin-bottom:4px;color:#182433;font-size:1rem}.address-login-link{display:inline-flex;align-items:center;justify-content:center;align-self:center;gap:8px;flex-shrink:0;padding:10px 14px;border-radius:999px;background:#fff;color:#111827;text-decoration:none;font-size:.9rem;font-weight:800;box-shadow:0 10px 18px rgba(15,23,42,.08)}.address-login-link:hover{color:#111827;transform:translateY(-1px)}@media (max-width:767.98px){.address-login-note{flex-direction:column;align-items:stretch}.address-login-link{justify-content:center}}
</style>
<style>
.addon-listing { height: auto !important; max-height: 240px !important; overflow-y: auto !important; border: 1px solid rgba(15,23,42,.12); border-radius: 14px; padding: 4px; background: #fff; margin-top: 8px; }
.addon-listing option { padding: 10px 14px; border-radius: 10px; margin-bottom: 2px; cursor: pointer; transition: all 0.15s ease; border-bottom: 1px solid #f8fafc; color: #334155; font-weight: 600; }
.addon-listing option:last-child { border-bottom: none; }
.addon-listing option:checked { background-color: var(--frontend-choose-btn-bg) !important; color: var(--frontend-choose-btn-text) !important; }
.addon-listing option:hover:not(:checked) { background-color: #f1f5f9; }
</style>
<style>
/* Product allergies pills (frontend menu) */
.product-allergies-wrap{margin-top:10px}
.product-allergies-tags{display:flex;flex-wrap:wrap;gap:8px}
.product-allergy-pill{animation:productAllergyIn .25s ease both}
@keyframes productAllergyIn{from{transform:translateY(4px);opacity:0}to{transform:translateY(0);opacity:1}}
.product-allergy-pill{appearance:none;border:1px solid rgba(255,202,39,.45);border-radius:999px;background:linear-gradient(135deg,rgba(255,202,39,.18),rgba(255,255,255,.95));color:#7c2d12;font-weight:900;font-size:.78rem;letter-spacing:.01em;padding:7px 10px;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,background .15s ease,border-color .15s ease}
.product-allergy-pill:hover{transform:translateY(-1px);box-shadow:0 14px 26px rgba(255,202,39,.22);background:linear-gradient(135deg,rgba(255,202,39,.25),rgba(255,255,255,.99));border-color:rgba(255,202,39,.65)}
.product-allergy-pill:focus-visible{outline:2px solid rgba(255,202,39,.65);outline-offset:2px}
.product-allergies-inline{display:inline;margin-left:5px;vertical-align:baseline;font-size:.72rem;color:#b45309;font-weight:700}
.product-allergy-pill--inline{display:inline;padding:0 !important;margin:0 !important;border:0 !important;background:transparent !important;box-shadow:none !important;border-radius:0 !important;color:inherit !important;font-size:inherit !important;font-weight:inherit !important;text-decoration:underline !important;text-decoration-style:solid !important;text-underline-offset:2px !important;transition:color .15s ease}
.product-allergy-pill--inline:hover{color:#d97706 !important}
.product-allergy-pill--inline:focus-visible{outline:1px solid rgba(217,119,6,.65);outline-offset:1px}
@media (max-width:767.98px){.product-allergies-tags{gap:6px}.product-allergy-pill{padding:6px 9px;font-size:.74rem}}
</style>
@endsection

@section('content')
<section class="menu-page">
    <div class="container">
        <div class="menu-shell">
            <div class="menu-grid">
                <div class="catalog-stack">
                    @foreach($categories as $index => $category)
                        <section class="catalog-section">
                            <button class="catalog-heading" type="button" data-bs-toggle="collapse" data-bs-target="#categoryPanel{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                <span>{{ $category->title }}</span>
                                <i class="fa-solid fa-plus"></i>
                            </button>
                            <div class="collapse {{ $index === 0 ? 'show' : '' }}" id="categoryPanel{{ $index }}">
                                <div class="catalog-panel">
                                    @if($category->products->isEmpty())
                                        <div class="product-copy">{{ __('frontend_no_products_available') }}</div>
                                    @else
                                        <div class="product-grid">
                                            @foreach($category->products as $product)
                                                <article class="product-card">
                                                    <div>
                                                        <div class="product-title">{{ $product->serial_number }} {{ $product->title }}</div>
                                                        <div class="product-copy">{!! $product->description !!}@if($product->allergies->isNotEmpty())<span class="product-allergies-inline" aria-label="{{ __('allergy_information') }}">
                                                    *@foreach($product->allergies as $allergy)@if(!$loop->first),@endif<button type="button" class="product-allergy-pill product-allergy-pill--inline js-open-allergy-popup" data-allergy-title="{{ $allergy->title }}" data-allergy-code="{{ $allergy->code }}">{{ $allergy->code }}</button>@endforeach
                                                </span>@endif</div>
                                                    </div>
                                                    <div class="product-price">{{ $currencySymbol . number_format(((float) $product->base_price) / $currencyRate, 2) }}</div>
                                                    <button type="button" class="choose-btn js-open-product-modal" data-product-id="{{ $product->id }}">{{ __('frontend_choose') }}</button>
                                                </article>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
                <aside class="basket-card" id="basket-panel">
                    <h2 class="basket-title">{{ __('frontend_shopping_basket') }}</h2>
                    <p class="basket-note {{ $frontendCustomerHasAddress ? 'd-none' : '' }}" id="basketNoteText">
                        <strong>{{ __('frontend_note') }}</strong>
                        {{ __('frontend_complete_address_note') }}
                    </p>
                    @if(! $frontendCustomerHasAddress)
                        <a href="#" class="basket-cta" id="completeAddressButton">{{ __('frontend_complete_address') }}</a>
                    @endif
                    <div class="basket-status {{ $frontendCustomerHasAddress ? 'is-visible' : '' }}" id="basketAddressStatus">{{ $frontendCustomerHasAddress ? __('frontend_address_saved') : '' }}</div>
                    <div id="basketItemsWrap"><div class="basket-empty">{{ __('frontend_basket_empty') }}</div></div>
                    <div id="basketSummaryWrap">
                        <div class="basket-summary-line basket-subtotal">
                            <span>{{ __('frontend_total') }}</span>
                            <span>{{ $currencySymbol }}0.00</span>
                        </div>
                    </div>
                    <p class="basket-note basket-min-order-note d-none" id="homeMinOrderNote"></p>
                    <div class="basket-links">
                        {{ __('frontend_more_info_prefix') }}
                        <a href="#">{{ __('frontend_shipping_costs') }}</a>,
                        <a href="#">{{ __('frontend_payment_methods') }}</a>
                        {{ __('frontend_and') }}
                        <a href="#">{{ __('frontend_cancellation_policy') }}</a>.
                    </div>
                    <div class="basket-actions">
                        <a href="{{ route('frontend.cart') }}" class="secondary"><span>{{ __('frontend_view_shopping_cart') }}</span></a>
                        <a href="{{ route('frontend.checkout') }}" class="primary" id="btnHomeToCheckout"><span>{{ __('frontend_to_checkout') }}</span></a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="allergyPopupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:24px; border:none; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#0f172a;">{{ __('allergy_information') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 480px; overflow-y: auto;">
                <div class="allergy-list-container">
                    @foreach($allAllergies as $allergy)
                        <div class="d-flex align-items-center gap-3 p-3 mb-3 js-allergy-row"
                             data-allergy-code="{{ $allergy->code }}"
                             style="background:#f8fafc; border-radius:18px; border:1px solid #f1f5f9; transition: all 0.3s ease;">
                            <div class="fw-black text-white d-flex align-items-center justify-content-center"
                                 style="width:54px; height:54px; border-radius:14px; background:var(--theme-primary); font-size:1.2rem; flex-shrink: 0;">
                                {{ $allergy->code }}
                            </div>
                            <div>
                                <div class="fw-bold" style="color:#1e293b; font-size:1.1rem;">{{ $allergy->title }}</div>
                                <div class="text-muted" style="font-size:0.85rem;">{{ __('allergy_code_label') }}: {{ $allergy->code }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade menu-modal" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="{{ $fallbackImage }}" alt="" class="modal-product-image" id="productModalImage">
                <div class="modal-product-summary">
                    <div class="modal-product-copy" id="productModalDescription"></div>
                    <div class="modal-price-pill" id="productModalPrice">{{ $currencySymbol }}0.00</div>
                </div>
                <form id="productModalForm">
                    <div id="productAddonFields"></div>
                    <div class="addon-block">
                        <label class="addon-title" for="productRemarks">{{ __('frontend_further_remarks') }}</label>
                        <textarea id="productRemarks" class="form-control addon-remarks" rows="4" placeholder="{{ __('frontend_add_more_notes') }}"></textarea>
                    </div>
                    <div class="modal-qty-row">
                        <button type="button" class="qty-btn" id="decreaseQty">-</button>
                        <div class="qty-display" id="productQtyDisplay">1</div>
                        <button type="button" class="qty-btn" id="increaseQty">+</button>
                    </div>
                    <button type="submit" class="modal-submit-btn">{{ __('frontend_add_to_cart') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade address-modal" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('frontend_complete_address') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="completeAddressForm">
                    <div class="address-login-note">
                        <div class="address-login-copy">
                            <strong>{{ __('frontend_returning_customer') }}</strong>
                            {{ __('frontend_returning_customer_note') }}
                        </div>
                        <a href="{{ route('login') }}" class="address-login-link">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>{{ __('frontend_click_here_to_login') }}</span>
                        </a>
                    </div>
                    <div class="address-grid">
                        <div>
                            <label class="address-form-label">{{ __('customer_type') }}</label>
                            <select class="form-select address-form-control customer-type-select" id="customerType" name="customer_type">
                                <option value="individual">{{ __('individual') }}</option>
                                <option value="company">{{ __('company') }}</option>
                            </select>
                        </div>
                        <div class="company-name-wrap" id="companyNameWrap">
                            <label class="address-form-label">{{ __('company_name') }}</label>
                            <input type="text" class="form-control address-form-control" name="company_name" id="companyName" placeholder="{{ __('company_name') }}">
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('first_name') }}*</label>
                            <input type="text" class="form-control address-form-control" name="first_name" placeholder="{{ __('first_name') }}" required>
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('last_name') }}*</label>
                            <input type="text" class="form-control address-form-control" name="last_name" placeholder="{{ __('last_name') }}" required>
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('email') }}*</label>
                            <input type="email" class="form-control address-form-control" name="email" id="frontendCustomerEmail" placeholder="{{ __('email') }}" required>
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('billing_phone') }}*</label>
                            <input type="text" class="form-control address-form-control" name="phone" placeholder="{{ __('billing_phone') }}" required>
                        </div>
                        <div class="address-full">
                            <label class="address-form-label">{{ __('frontend_address_search') }}</label>
                            <div class="address-search-wrap">
                                <input type="text" class="form-control address-search-box" id="frontendAddressSearch" name="address" placeholder="{{ __('frontend_address_search_placeholder') }}" required>
                                <div class="address-suggestions" id="frontendAddressSuggestions"></div>
                            </div>
                            <div class="address-help">{{ __('frontend_address_search_help') }}</div>
                        </div>
                        <div class="address-full">
                            <div class="address-map-card">
                                <div class="address-map-head">
                                    <span>{{ __('frontend_pin_location') }}</span>
                                    <small>{{ __('frontend_pin_location_help') }}</small>
                                </div>
                                <div id="frontendAddressMap" class="address-map-canvas"></div>
                                <div class="address-map-loader" id="frontendAddressMapLoader">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <span>{{ __('frontend_updating_address_from_map') }}</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('city') }}*</label>
                            <input type="text" class="form-control address-form-control" name="city" id="frontendCity" placeholder="{{ __('city') }}" required>
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('billing_postal_code') }}*</label>
                            <input type="text" class="form-control address-form-control" name="postal_code" id="frontendPostalCode" placeholder="{{ __('billing_postal_code') }}" required>
                        </div>
                        <div class="address-full">
                            <label class="address-form-label">{{ __('country') }}*</label>
                            <select class="form-select address-form-control" name="country_id" id="frontendCountrySelect" required>
                                <option value="">{{ __('frontend_select_country') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" data-country-name="{{ strtolower($country->name) }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="country" id="frontendCountryName">
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('password') }}*</label>
                            <input type="password" class="form-control address-form-control" name="password" placeholder="{{ __('password') }}" required>
                        </div>
                        <div>
                            <label class="address-form-label">{{ __('confirm_password') }}*</label>
                            <input type="password" class="form-control address-form-control" name="password_confirmation" placeholder="{{ __('confirm_password') }}" required>
                        </div>
                    </div>
                    <input type="hidden" name="latitude" id="frontendLatitude">
                    <input type="hidden" name="longitude" id="frontendLongitude">
                    <div class="address-feedback" id="addressFormFeedback"></div>
                    <button type="submit" class="address-submit-btn mt-4">{{ __('frontend_complete_address') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($mapProvider === 'leaflet')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif
@if($mapProvider === 'google' && filled($googleMapsApiKey))
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places"></script>
@endif
<script src="{{ asset('public/assets/js/select2/select2.full.min.js') }}"></script>
<script>
(() => {
const products = @json($menuProducts), currencyRate = {{ json_encode($currencyRate) }}, currencySymbol = @json($currencySymbol);
const activeTax = @json($activeTax ? ['title' => $activeTax->title, 'amount' => (float) $activeTax->amount, 'calculation_type' => $activeTax->calculation_type] : null);
const frontendCustomer = @json($frontendCustomerData);
const mapProvider = @json($mapProvider);
const routes = {completeAddress:@json(route('frontend.complete-address')),checkCustomerEmail:@json(route('frontend.check-customer-email')),login:@json(route('login')),deliverySummary:@json(route('frontend.checkout.delivery-summary')),addressSearch:@json(route('frontend.address-search')),geocodeAddress:@json(route('frontend.geocode-address')),reverseGeocodeAddress:@json(route('frontend.reverse-geocode-address'))};
const labels = {chooseOption:@json(__('frontend_choose_option')),multiSelectHelp:@json(__('frontend_multi_select_help')),furtherRemarks:@json(__('frontend_further_remarks')),shipSoon:@json(__('frontend_ship_as_soon_as_possible')),includesTax:@json(__('frontend_includes_tax')),shippingCosts:@json(__('frontend_shipping_costs')),subtotal:@json(__('frontend_subtotal')),total:@json(__('frontend_total')),vat:@json(__('frontend_vat')),basketEmpty:@json(__('frontend_basket_empty')),distance:@json(__('frontend_delivery_distance')),addressSaved:@json(__('frontend_address_saved')),addressSavedNote:@json(__('frontend_address_saved_note')),completeAddressNote:@json(__('frontend_complete_address_note')),addressLookupFailed:@json(__('frontend_address_lookup_failed')),selectCountry:@json(__('frontend_select_country')),savingAddress:@json(__('frontend_saving_address')),addressCompleted:@json(__('frontend_address_completed_successfully')),dragPin:@json(__('frontend_drag_pin_help')),emailExistsPrompt:@json(__('frontend_email_exists_prompt')),clickHereToLogin:@json(__('frontend_click_here_to_login'))};
const minOrderMessageTemplate = @json(__('frontend_min_order_amount_note'));
const homeMinOrderNote = document.getElementById('homeMinOrderNote');
const btnHomeToCheckout = document.getElementById('btnHomeToCheckout');
let homeMinOrderBlocked = false;
const storageKey='foodshop_frontend_cart',modalEl=document.getElementById('productModal'),modal=new bootstrap.Modal(modalEl),addressModalEl=document.getElementById('addressModal'),addressModal=addressModalEl?new bootstrap.Modal(addressModalEl):null,allergyModalEl=document.getElementById('allergyPopupModal'),allergyModal=allergyModalEl?new bootstrap.Modal(allergyModalEl):null;
const allergyPopupTitle=document.getElementById('allergyPopupTitle'),allergyPopupCode=document.getElementById('allergyPopupCode');
const modalTitle=document.getElementById('productModalTitle'),modalImage=document.getElementById('productModalImage'),modalDescription=document.getElementById('productModalDescription'),modalPrice=document.getElementById('productModalPrice'),addonFields=document.getElementById('productAddonFields'),remarks=document.getElementById('productRemarks'),qtyDisplay=document.getElementById('productQtyDisplay'),modalForm=document.getElementById('productModalForm'),basketItemsWrap=document.getElementById('basketItemsWrap'),basketSummaryWrap=document.getElementById('basketSummaryWrap'),cartBadge=document.querySelector('.portal-cart-badge');
const completeAddressButton=document.getElementById('completeAddressButton'),basketAddressStatus=document.getElementById('basketAddressStatus'),basketNoteText=document.getElementById('basketNoteText'),addressForm=document.getElementById('completeAddressForm'),addressFeedback=document.getElementById('addressFormFeedback'),companyNameWrap=document.getElementById('companyNameWrap'),customerTypeField=document.getElementById('customerType'),countrySelect=document.getElementById('frontendCountrySelect'),countryNameField=document.getElementById('frontendCountryName'),addressSearchField=document.getElementById('frontendAddressSearch'),addressSuggestionsEl=document.getElementById('frontendAddressSuggestions'),frontendAddressField=addressSearchField,frontendCityField=document.getElementById('frontendCity'),frontendPostalField=document.getElementById('frontendPostalCode'),frontendLatitudeField=document.getElementById('frontendLatitude'),frontendLongitudeField=document.getElementById('frontendLongitude'),frontendAddressMap=document.getElementById('frontendAddressMap'),frontendAddressMapLoader=document.getElementById('frontendAddressMapLoader'),frontendCustomerEmail=document.getElementById('frontendCustomerEmail');
let activeProduct=null,activeQty=1,cart=loadCart(),currentCustomer=frontendCustomer,deliverySummary={shipping_cost:0,distance_km:null,branch:null,has_address:Boolean(frontendCustomer)},addressSearchTimer=null,addressMap=null,addressMarker=null,addressGeocoder=null,addressInfoWindow=null,addressReverseLookupTimer=null,googleAutocomplete=null,addressSearchInitialized=false,emailCheckTimer=null;
function money(v){return currencySymbol+(Number(v/currencyRate).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}))}
function loadCart(){try{return JSON.parse(sessionStorage.getItem(storageKey))||[]}catch(e){return []}}
function saveCart(){sessionStorage.setItem(storageKey,JSON.stringify(cart))}
function subtotalAmount(){return cart.reduce((sum,item)=>sum+Number(item.line_total),0)}
function customerHasAddress(customer){return Boolean(customer&&(customer.has_address||(customer.address&&customer.city&&customer.postal_code&&(customer.country_id||customer.country))))}
function setMapLoader(visible){if(!frontendAddressMapLoader){return}frontendAddressMapLoader.classList.toggle('is-visible',visible)}
function initAddonSelect2(){if(!window.jQuery||!jQuery.fn.select2){return}jQuery(addonFields).find('select[data-selection-type="multiple"]').each(function(){const $select=jQuery(this);if($select.hasClass('select2-hidden-accessible')){$select.select2('destroy')}$select.select2({width:'100%',closeOnSelect:false,dropdownParent:jQuery(modalEl),placeholder:labels.chooseOption})}).on('change',renderModalPrice)}
function selectedAddonTotal(){let total=0;addonFields.querySelectorAll('[data-addon-select]').forEach((field)=>{const opts=field.multiple?Array.from(field.selectedOptions):(field.value?[field.selectedOptions[0]]:[]);opts.forEach((opt)=>total+=Number(opt.dataset.price||0))});return total}
function renderModalPrice(){if(!activeProduct){modalPrice.textContent=money(0);return}modalPrice.textContent=money((Number(activeProduct.base_price)+selectedAddonTotal())*activeQty)}
function addonField(addon){const wrap=document.createElement('div');wrap.className='addon-block';const label=document.createElement('label');label.className='addon-title';label.textContent=addon.title;wrap.appendChild(label);const select=document.createElement('select');select.className='form-select addon-select';select.dataset.addonSelect='true';select.dataset.addonId=addon.id;select.dataset.addonTitle=addon.title;select.dataset.selectionType=addon.selection_type;if(addon.selection_type==='multiple'){select.multiple=true;select.dataset.selectionType='multiple'}else if(addon.selection_type==='listing'){select.multiple=true;select.size=8;select.classList.add('addon-listing');select.dataset.selectionType='listing'}else{const p=document.createElement('option');p.value='';p.textContent=labels.chooseOption;select.appendChild(p)}addon.values.forEach((value)=>{const o=document.createElement('option');o.value=String(value.id);o.dataset.price=String(value.price||0);o.textContent=value.title+(Number(value.price)>0?` (+${money(value.price)})`:'');select.appendChild(o)});if(addon.selection_type==='listing'){select.addEventListener('mousedown',function(e){if(e.target.tagName==='OPTION'){e.preventDefault();const scroll=this.scrollTop;e.target.selected=!e.target.selected;setTimeout(()=>{this.scrollTop=scroll;this.focus()},0);this.dispatchEvent(new Event('change',{bubbles:true}))}})}select.addEventListener('change',renderModalPrice);wrap.appendChild(select);if(addon.selection_type==='multiple'){const help=document.createElement('div');help.className='addon-help';help.textContent=labels.multiSelectHelp;wrap.appendChild(help)}return wrap}
function openModal(productId){activeProduct=products.find((p)=>Number(p.id)===Number(productId))||null;activeQty=1;if(!activeProduct){return}modalTitle.textContent=`${activeProduct.serial_number} ${activeProduct.title}`;modalImage.src=activeProduct.image_url;modalImage.alt=activeProduct.title;modalDescription.innerHTML=activeProduct.description||'';addonFields.innerHTML='';remarks.value='';qtyDisplay.textContent='1';activeProduct.addons.forEach((addon)=>addonFields.appendChild(addonField(addon)));initAddonSelect2();renderModalPrice();modal.show()}
function collectAddons(){const selections=[];let addonTotal=0;addonFields.querySelectorAll('[data-addon-select]').forEach((field)=>{const items=field.multiple?Array.from(field.selectedOptions):(field.value?[field.selectedOptions[0]]:[]);if(!items.length){return}const values=items.map((opt)=>{const price=Number(opt.dataset.price||0);addonTotal+=price;return{id:Number(opt.value),title:opt.textContent.replace(/\s\(\+.*\)$/,''),price}});selections.push({addon_id:Number(field.dataset.addonId),title:field.dataset.addonTitle,selection_type:field.dataset.selectionType,values})});return{selections,addonTotal}}
function hideAddressSuggestions(){if(addressSuggestionsEl){addressSuggestionsEl.innerHTML='';addressSuggestionsEl.classList.remove('is-visible');addressSuggestionsEl.dataset.results='[]'}}
function showAddressFeedback(message,type='error'){if(!addressFeedback){return}addressFeedback.textContent=message;addressFeedback.className=`address-feedback is-visible ${type==='success'?'is-success':'is-error'}`}
function clearAddressFeedback(){if(!addressFeedback){return}addressFeedback.textContent='';addressFeedback.className='address-feedback'}
function showAddressFeedbackHtml(message,type='error'){if(!addressFeedback){return}addressFeedback.innerHTML=message;addressFeedback.className=`address-feedback is-visible ${type==='success'?'is-success':'is-error'}`}
function existingEmailMessage(){return `${labels.emailExistsPrompt} <a href="${routes.login}" class="fw-bold text-decoration-underline">${labels.clickHereToLogin}</a>`}
async function checkCustomerEmailExists(){if(!frontendCustomerEmail){return false}const email=frontendCustomerEmail.value.trim();if(!email||!/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email)){return false}try{const response=await fetch(routes.checkCustomerEmail,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({email})});const payload=await response.json();if(Boolean(payload.exists)){showAddressFeedbackHtml(existingEmailMessage());return true}if(addressFeedback&&addressFeedback.classList.contains('is-error')&&addressFeedback.innerHTML.includes(routes.login)){clearAddressFeedback()}return false}catch(error){return false}}
function setCountryByName(countryName){if(!countrySelect){return}const normalized=(countryName||'').trim().toLowerCase();countryNameField.value=countryName||'';const option=Array.from(countrySelect.options).find((item)=>item.dataset.countryName===normalized||item.text.trim().toLowerCase()===normalized);jQuery(countrySelect).val(option?option.value:'').trigger('change')}
function scheduleReverseGeocode(latitude,longitude){clearTimeout(addressReverseLookupTimer);setMapLoader(true);addressReverseLookupTimer=setTimeout(()=>reverseGeocodeFromMap(latitude,longitude),250)}
function updateMapFromCoordinates(latitude,longitude,withLookup=false){const lat=Number(latitude),lng=Number(longitude);if(!frontendAddressMap||!Number.isFinite(lat)||!Number.isFinite(lng)){return}if(mapProvider==='google'&&typeof google!=='undefined'&&google.maps){if(!addressMap){addressMap=new google.maps.Map(frontendAddressMap,{center:{lat,lng},zoom:16,mapTypeControl:false,streetViewControl:false,fullscreenControl:false});addressMarker=new google.maps.Marker({map:addressMap,position:{lat,lng},draggable:true});addressInfoWindow=new google.maps.InfoWindow({content:`<div style="font-weight:700;">${labels.dragPin}</div>`});addressInfoWindow.open({anchor:addressMarker,map:addressMap,shouldFocus:false});addressMarker.addListener('dragend',()=>{const position=addressMarker.getPosition();if(!position){return}frontendLatitudeField.value=String(position.lat());frontendLongitudeField.value=String(position.lng());scheduleReverseGeocode(position.lat(),position.lng())})}addressMap.setCenter({lat,lng});addressMarker.setPosition({lat,lng});if(withLookup){scheduleReverseGeocode(lat,lng)}return}if(mapProvider==='leaflet'&&typeof L!=='undefined'){if(!addressMap){addressMap=L.map(frontendAddressMap,{zoomControl:true}).setView([lat,lng],16);L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(addressMap);addressMarker=L.marker([lat,lng],{draggable:true}).addTo(addressMap).bindPopup(labels.dragPin).openPopup();addressMarker.on('dragend',()=>{const markerLatLng=addressMarker.getLatLng();frontendLatitudeField.value=String(markerLatLng.lat);frontendLongitudeField.value=String(markerLatLng.lng);scheduleReverseGeocode(markerLatLng.lat,markerLatLng.lng)})}addressMap.setView([lat,lng],16);addressMarker.setLatLng([lat,lng]);if(withLookup){scheduleReverseGeocode(lat,lng)}setTimeout(()=>addressMap.invalidateSize(),150)}}
async function reverseGeocodeFromMap(latitude,longitude){try{if(mapProvider==='google'&&typeof google!=='undefined'&&google.maps){addressGeocoder=addressGeocoder||new google.maps.Geocoder();addressGeocoder.geocode({location:{lat:Number(latitude),lng:Number(longitude)}},(results,status)=>{if(status==='OK'&&Array.isArray(results)&&results.length){parseGooglePlace({formatted_address:results[0].formatted_address,geometry:{location:{lat:()=>Number(latitude),lng:()=>Number(longitude)}},address_components:results[0].address_components||[],name:results[0].formatted_address})}setMapLoader(false)});return}const response=await fetch(routes.reverseGeocodeAddress,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({latitude,longitude})});const payload=await response.json();if(payload.result){fillAddressFields(payload.result,false)}}catch(error){}finally{if(mapProvider!=='google'){setMapLoader(false)}}}
function fillAddressFields(result,updateMap=true){if(!result){return}frontendAddressField.value=result.address||result.label||'';frontendCityField.value=result.city||'';frontendPostalField.value=result.postal_code||'';frontendLatitudeField.value=result.latitude||'';frontendLongitudeField.value=result.longitude||'';setCountryByName(result.country||'');addressSearchField.value=result.address||result.label||addressSearchField.value;hideAddressSuggestions();if(updateMap&&result.latitude&&result.longitude){updateMapFromCoordinates(result.latitude,result.longitude,false)}}
function renderAddressSuggestions(results){if(!addressSuggestionsEl){return}addressSuggestionsEl.dataset.results=JSON.stringify(results||[]);if(!results||!results.length){addressSuggestionsEl.innerHTML=`<div class="address-suggestion"><span class="address-suggestion-copy">${labels.addressLookupFailed}</span></div>`;addressSuggestionsEl.classList.add('is-visible');return}addressSuggestionsEl.innerHTML=results.map((result,index)=>`<div class="address-suggestion" data-result-index="${index}"><span class="address-suggestion-title">${result.title||result.address||result.label}</span><span class="address-suggestion-copy">${result.label||result.address||''}</span></div>`).join('');addressSuggestionsEl.classList.add('is-visible')}
async function searchLeafletAddresses(query){const response=await fetch(`${routes.addressSearch}?q=${encodeURIComponent(query)}`,{headers:{Accept:'application/json'}});const payload=await response.json();renderAddressSuggestions(payload.results||[])}
async function geocodeLeafletAddress(){const response=await fetch(routes.geocodeAddress,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({address:frontendAddressField.value,city:frontendCityField.value,postal_code:frontendPostalField.value,country:countryNameField.value})});const payload=await response.json();if(payload.result){fillAddressFields(payload.result)}}
function parseGooglePlace(place){if(!place||!place.geometry){return}const byType=(type)=>{const component=(place.address_components||[]).find((item)=>item.types.includes(type));return component?component.long_name:''};fillAddressFields({address:place.formatted_address||addressSearchField.value,label:place.formatted_address||addressSearchField.value,title:place.name||'',latitude:place.geometry.location.lat(),longitude:place.geometry.location.lng(),city:byType('locality')||byType('administrative_area_level_2'),postal_code:byType('postal_code'),country:byType('country')})}
function initAddressMap(){const lat=Number(frontendLatitudeField?.value||24.8607),lng=Number(frontendLongitudeField?.value||67.0011);updateMapFromCoordinates(lat,lng,false)}
function initAddressSearch(){if(!addressSearchField||addressSearchInitialized&&mapProvider!=='google'){return}if(mapProvider==='google'&&typeof google!=='undefined'&&google.maps){if(googleAutocomplete){return}googleAutocomplete=new google.maps.places.Autocomplete(addressSearchField,{fields:['formatted_address','geometry','address_components','name'],types:['geocode']});googleAutocomplete.addListener('place_changed',()=>parseGooglePlace(googleAutocomplete.getPlace()));return}if(addressSearchInitialized){return}addressSearchInitialized=true;addressSearchField.addEventListener('input',()=>{frontendLatitudeField.value='';frontendLongitudeField.value='';clearTimeout(addressSearchTimer);const query=addressSearchField.value.trim();if(query.length<3){hideAddressSuggestions();return}addressSearchTimer=setTimeout(()=>searchLeafletAddresses(query).catch(()=>renderAddressSuggestions([])),280)})}
async function fetchDeliverySummary(){if(!customerHasAddress(currentCustomer)||subtotalAmount()<=0){deliverySummary={shipping_cost:0,distance_km:null,branch:null,has_address:Boolean(customerHasAddress(currentCustomer))};renderBasket();evaluateHomeMinimumOrder(subtotalAmount());return}try{const response=await fetch(routes.deliverySummary,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({subtotal:subtotalAmount(),order_type:'delivery'})});const payload=await response.json();deliverySummary=payload.summary||deliverySummary}catch(error){deliverySummary={shipping_cost:0,distance_km:null,branch:null,has_address:Boolean(customerHasAddress(currentCustomer))}}renderBasket();evaluateHomeMinimumOrder(subtotalAmount())}
function refreshHomeCheckoutButtonState(){if(!btnHomeToCheckout){return}if(homeMinOrderBlocked){btnHomeToCheckout.classList.add('disabled');btnHomeToCheckout.setAttribute('aria-disabled','true');btnHomeToCheckout.style.pointerEvents='none';return}btnHomeToCheckout.classList.remove('disabled');btnHomeToCheckout.removeAttribute('aria-disabled');btnHomeToCheckout.style.pointerEvents=''}
function evaluateHomeMinimumOrder(subtotal){if(!homeMinOrderNote){homeMinOrderBlocked=false;refreshHomeCheckoutButtonState();return}if(subtotal<=0){homeMinOrderNote.classList.add('d-none');homeMinOrderBlocked=false;refreshHomeCheckoutButtonState();return}const minAmount=Number(deliverySummary?.shipping_fee?.min_order_amount??0);const requiredMore=Math.max(minAmount-subtotal,0);if(minAmount>0&&requiredMore>0){homeMinOrderNote.textContent=minOrderMessageTemplate.replace(':amount',money(requiredMore));homeMinOrderNote.classList.remove('d-none');homeMinOrderBlocked=true;refreshHomeCheckoutButtonState();return}homeMinOrderNote.classList.add('d-none');homeMinOrderBlocked=false;refreshHomeCheckoutButtonState()}
function renderBasket(){if(!cart.length){basketItemsWrap.innerHTML=`<div class="basket-empty">${labels.basketEmpty}</div>`}else{basketItemsWrap.innerHTML=cart.map((item,index)=>{const addonLines=item.addons.map((addon)=>`${addon.title}: ${addon.values.map((value)=>value.title).join(', ')}`).join('<br>');const remarksLine=item.remarks?`${labels.furtherRemarks}: ${item.remarks}`:'';const meta=[addonLines,remarksLine].filter(Boolean).join('<br>');return `<div class="basket-item"><button type="button" class="basket-remove js-remove-cart-item" data-cart-index="${index}"><i class="fa-solid fa-xmark"></i></button><div><div class="basket-item-title">${item.serial_number} ${item.title}</div><div class="basket-meta">${meta||labels.shipSoon}</div></div></div><div class="basket-summary-line"><span>${item.quantity} x ${money(item.unit_total)}</span><span>${money(item.line_total)}</span></div>`}).join('')}const hasSavedAddress=customerHasAddress(currentCustomer);const subtotal=subtotalAmount();const shippingAmount=subtotal>0&&hasSavedAddress?Number(deliverySummary.shipping_cost||0):0;const vatAmount=subtotal>0&&activeTax?(activeTax.calculation_type==='percentage'?subtotal*(Number(activeTax.amount)/100):Number(activeTax.amount)):0;const totalAmount=subtotal+shippingAmount+vatAmount;let summary='';if(subtotal>0){summary+=`<div class="basket-summary-line"><span>${labels.subtotal}</span><span>${money(subtotal)}</span></div>`;summary+=`<div class="basket-summary-line"><span>${labels.shippingCosts}</span><span>${money(shippingAmount)}</span></div>`;if(vatAmount>0||activeTax){summary+=`<div class="basket-summary-line"><span>${labels.vat}</span><span>${activeTax&&activeTax.calculation_type==='percentage'?`${money(vatAmount)} (${String(activeTax.amount).replace(/\.00$/,'')}%)`:money(vatAmount)}</span></div>`}summary+=`<div class="basket-summary-line basket-subtotal"><span>${labels.total}</span><span>${money(totalAmount)}</span></div>`;if(hasSavedAddress&&deliverySummary.distance_km!==null){summary+=`<div class="basket-summary-line"><span>${labels.distance}</span><span>${Number(deliverySummary.distance_km).toFixed(2)} km</span></div>`}}else{summary=`<div class="basket-summary-line basket-subtotal"><span>${labels.total}</span><span>${money(0)}</span></div>`}basketSummaryWrap.innerHTML=summary;if(cartBadge){cartBadge.textContent=String(cart.reduce((sum,item)=>sum+Number(item.quantity),0))}if(basketAddressStatus){basketAddressStatus.textContent=hasSavedAddress?labels.addressSaved:'';basketAddressStatus.classList.toggle('is-visible',hasSavedAddress)}if(basketNoteText){basketNoteText.innerHTML=`<strong>{{ __('frontend_note') }}</strong> ${labels.completeAddressNote}`;basketNoteText.classList.toggle('d-none',hasSavedAddress)}if(completeAddressButton){completeAddressButton.style.display=hasSavedAddress?'none':'inline-flex'}}
async function submitCompleteAddress(event){event.preventDefault();clearAddressFeedback();if(await checkCustomerEmailExists()){return}countryNameField.value=countrySelect.options[countrySelect.selectedIndex]?.text||countryNameField.value;if(mapProvider==='leaflet'&&!frontendLatitudeField.value&&frontendAddressField.value.trim()){await geocodeLeafletAddress()}const formData=new FormData(addressForm);formData.set('address',frontendAddressField.value);formData.append('subtotal',String(subtotalAmount()));formData.append('order_type','delivery');const submitButton=addressForm.querySelector('button[type="submit"]');const defaultText=submitButton.textContent;submitButton.disabled=true;submitButton.textContent=labels.savingAddress;try{const response=await fetch(routes.completeAddress,{method:'POST',headers:{Accept:'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:formData});const payload=await response.json();if(!response.ok){if(payload.errors&&payload.errors.email){showAddressFeedbackHtml(existingEmailMessage());submitButton.disabled=false;submitButton.textContent=defaultText;return}const message=payload.errors?Object.values(payload.errors)[0][0]:(payload.message||labels.addressLookupFailed);showAddressFeedback(message);submitButton.disabled=false;submitButton.textContent=defaultText;return}currentCustomer=payload.customer||currentCustomer;deliverySummary=payload.delivery_summary||deliverySummary;if(completeAddressButton){completeAddressButton.style.display='none'}renderBasket();evaluateHomeMinimumOrder(subtotalAmount());showAddressFeedback(payload.message||labels.addressCompleted,'success');setTimeout(()=>{addressModal.hide();addressForm.reset();companyNameWrap.style.display='none';hideAddressSuggestions();clearAddressFeedback();submitButton.disabled=false;submitButton.textContent=defaultText;jQuery(countrySelect).val('').trigger('change')},700)}catch(error){showAddressFeedback(labels.addressLookupFailed);submitButton.disabled=false;submitButton.textContent=defaultText}}
document.querySelectorAll('.js-open-product-modal').forEach((btn)=>btn.addEventListener('click',()=>openModal(btn.dataset.productId)));
document.querySelectorAll('.js-open-allergy-popup').forEach((badge)=>{
    badge.addEventListener('click',(e)=>{
        e.stopPropagation();
        const code = badge.dataset.allergyCode;
        if(allergyModal) allergyModal.show();

        // Highlight and scroll to the clicked allergy after modal is shown
        setTimeout(() => {
            const rows = document.querySelectorAll('.js-allergy-row');
            rows.forEach(row => {
                row.style.background = '#f8fafc'; // Reset
                row.style.borderColor = '#f1f5f9';
                if(row.dataset.allergyCode === code) {
                    row.style.background = 'rgba(255, 255, 255, 0.1)';
                    row.style.borderColor = 'var(--theme-primary)';
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }, 300);
    });
});
document.getElementById('decreaseQty').addEventListener('click',()=>{activeQty=Math.max(1,activeQty-1);qtyDisplay.textContent=String(activeQty);renderModalPrice()});
document.getElementById('increaseQty').addEventListener('click',()=>{activeQty+=1;qtyDisplay.textContent=String(activeQty);renderModalPrice()});
modalForm.addEventListener('submit',(e)=>{e.preventDefault();if(!activeProduct){return}const {selections,addonTotal}=collectAddons();const unitTotal=Number(activeProduct.base_price)+addonTotal;cart.push({product_id:activeProduct.id,serial_number:activeProduct.serial_number,title:activeProduct.title,image_url:activeProduct.image_url,description:activeProduct.description||'',quantity:activeQty,unit_total:unitTotal,line_total:unitTotal*activeQty,remarks:remarks.value.trim(),addons:selections});saveCart();renderBasket();evaluateHomeMinimumOrder(subtotalAmount());if(currentCustomer){fetchDeliverySummary()}modal.hide()});
document.addEventListener('click',(e)=>{const removeBtn=e.target.closest('.js-remove-cart-item');if(removeBtn){cart.splice(Number(removeBtn.dataset.cartIndex),1);saveCart();renderBasket();evaluateHomeMinimumOrder(subtotalAmount());if(currentCustomer){fetchDeliverySummary()}return}const suggestion=e.target.closest('.address-suggestion');if(!suggestion){if(!e.target.closest('.address-search-wrap')){hideAddressSuggestions()}return}const results=JSON.parse(addressSuggestionsEl.dataset.results||'[]');fillAddressFields(results[Number(suggestion.dataset.resultIndex)]||null)});
jQuery(modalEl).on('hidden.bs.modal',function(){jQuery(addonFields).find('select[data-selection-type="multiple"]').each(function(){const $select=jQuery(this);if($select.hasClass('select2-hidden-accessible')){$select.select2('destroy')}})});
if(addressForm){addressForm.addEventListener('submit',submitCompleteAddress)}
if(frontendCustomerEmail){frontendCustomerEmail.addEventListener('input',()=>{clearTimeout(emailCheckTimer);emailCheckTimer=setTimeout(()=>{checkCustomerEmailExists()},350)});frontendCustomerEmail.addEventListener('blur',()=>{checkCustomerEmailExists()})}
if(customerTypeField){customerTypeField.addEventListener('change',()=>{companyNameWrap.style.display=customerTypeField.value==='company'?'block':'none';if(customerTypeField.value!=='company'){document.getElementById('companyName').value=''}})}
if(countrySelect&&window.jQuery){jQuery(countrySelect).select2({width:'100%',dropdownParent:jQuery(addressModalEl),placeholder:labels.selectCountry}).on('change',function(){countryNameField.value=this.options[this.selectedIndex]?.text||''})}
if(completeAddressButton){completeAddressButton.addEventListener('click',(e)=>{e.preventDefault();if(addressModal){addressModal.show()}})}
if(addressModalEl){addressModalEl.addEventListener('shown.bs.modal',()=>{initAddressSearch();initAddressMap();if(mapProvider==='leaflet'&&addressMap&&typeof addressMap.invalidateSize==='function'){setTimeout(()=>addressMap.invalidateSize(),180)}if(mapProvider==='google'){setTimeout(()=>addressSearchField?.focus(),160)}})}
initAddressSearch();
if(btnHomeToCheckout){btnHomeToCheckout.addEventListener('click',(e)=>{if(cart.length===0){e.preventDefault();alert(labels.basketEmpty);return}if(homeMinOrderBlocked){e.preventDefault();const minAmount=Math.max(Number(deliverySummary?.shipping_fee?.min_order_amount??0)-subtotalAmount(),0);const message=homeMinOrderNote?.textContent||minOrderMessageTemplate.replace(':amount',money(minAmount));alert(message);return}if(!customerHasAddress(currentCustomer)){e.preventDefault();if(addressModal){addressModal.show()}}})}
if(btnHomeToCheckout){btnHomeToCheckout.addEventListener('click',(e)=>{if(cart.length===0){e.preventDefault();alert(labels.basketEmpty);return}if(homeMinOrderBlocked){e.preventDefault();const minAmount=Math.max(Number(deliverySummary?.shipping_fee?.min_order_amount??0)-subtotalAmount(),0);const message=homeMinOrderNote?.textContent||minOrderMessageTemplate.replace(':amount',money(minAmount));alert(message);return}if(!customerHasAddress(currentCustomer)){e.preventDefault();if(addressModal){addressModal.show()}}})}
renderBasket();evaluateHomeMinimumOrder(subtotalAmount());
if(customerHasAddress(currentCustomer)&&cart.length){fetchDeliverySummary()}
})();
</script>
@endsection



