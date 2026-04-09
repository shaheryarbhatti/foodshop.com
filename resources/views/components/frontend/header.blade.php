@php
    $locales = ['en' => 'English', 'id' => 'Indonesia'];
    $currentLocale = app()->getLocale();
    $currencies = \App\Models\Currency::orderBy('name')->get();
    $currentCurrency = \App\Models\Currency::find(session('frontend_currency_id')) ?: \App\Models\Currency::active()->first() ?: $currencies->first();
    $frontendCustomerId = session('frontend_customer_id');
    $frontendHeaderLogo = \App\Models\Setting::get('frontend_header_logo', 'assets/images/logo/logo.png');
    $frontendHeaderLogoUrl = asset('public/' . $frontendHeaderLogo);
    $frontendHeaderOverlayColor = \App\Models\Setting::get('frontend_header_logo_overlay_color', '');
    $frontendHeaderOverlayClass = $frontendHeaderOverlayColor ? 'has-overlay' : '';
@endphp
<nav class="navbar navbar-expand-lg portal-nav">
    <div class="container">
        <a class="brand-link" href="{{ route('frontend.home') }}">
            <span class="frontend-logo-wrap {{ $frontendHeaderOverlayClass }}"
                style="--frontend-logo-color: {{ $frontendHeaderOverlayColor ?: 'transparent' }}; --frontend-logo-url: url('{{ $frontendHeaderLogoUrl }}');">
                <img class="frontend-logo-img" src="{{ $frontendHeaderLogoUrl }}" alt="Frontend Header Logo">
                <span class="frontend-logo-overlay-mask" aria-hidden="true"></span>
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#frontendNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="frontendNavbar">
            <div class="nav-links mx-lg-5">
                <a href="{{ route('frontend.home') }}" class="{{ ($activeNav ?? '') === 'home' ? 'active' : '' }}">{{ __('frontend_homepage') }}</a>
                @if($frontendCustomerId)
                    <a href="{{ route('frontend.dashboard') }}" class="{{ ($activeNav ?? '') === 'dashboard' ? 'active' : '' }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="{{ ($activeNav ?? '') === 'submit-ticket' ? 'active' : '' }}">{{ __('frontend_login_register') }}</a>
                @endif
                <a href="{{ route('admin.login') }}" class="{{ ($activeNav ?? '') === 'admin-panel' ? 'active' : '' }}">{{ __('frontend_admin_panel') }}</a>
            </div>
            <div class="ms-lg-auto d-flex flex-column flex-lg-row align-items-lg-center gap-2 mt-3 mt-lg-0">
                <div class="portal-control-shell">
                    <span class="portal-control-icon"><i class="fa-solid fa-globe"></i></span>
                    <select class="portal-control" onchange="if(this.value){window.location=this.value;}">
                        @foreach($locales as $code => $label)
                            <option value="{{ route('language.switch', $code) }}" {{ $currentLocale === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="portal-control-shell">
                    <span class="portal-control-icon"><i class="fa-solid fa-coins"></i></span>
                    <select class="portal-control" onchange="if(this.value){window.location=this.value;}">
                        @foreach($currencies as $currency)
                            <option value="{{ route('frontend.currency.switch', $currency->id) }}" {{ $currentCurrency && $currentCurrency->id === $currency->id ? 'selected' : '' }}>
                                {{ $currency->code }}{{ $currency->symbol ? ' - ' . $currency->symbol : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('frontend.cart') }}" class="portal-cart text-decoration-none">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="portal-cart-badge">0</span>
                </a>
                @if($frontendCustomerId)
                    <form method="POST" action="{{ route('frontend.logout') }}" class="mb-0">
                        @csrf
                        <button type="submit" class="portal-cart text-decoration-none border-0">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</nav>
