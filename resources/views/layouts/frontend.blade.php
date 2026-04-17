<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        $frontendBackground = \App\Models\Setting::get('frontend_background_image', 'assets/images/login/login_image.jpg');
        $frontendBackgroundUrl = asset('public/' . $frontendBackground);
        $frontendHeaderBgColor = \App\Models\Setting::get('frontend_header_bg_color', '#070708');
        $frontendHeaderTextColor = \App\Models\Setting::get('frontend_header_text_color', '#ffffff');
        $frontendFooterBgColor = \App\Models\Setting::get('frontend_footer_bg_color', '#000000');
        $frontendFooterTextColor = \App\Models\Setting::get('frontend_footer_text_color', '#ffffff');
        $metaTitle = trim((string) \App\Models\Setting::get('meta_title', 'WooFood'));
        $metaKeywords = trim((string) \App\Models\Setting::get('meta_keywords', 'food ordering, online restaurant, delivery, pickup'));
        $metaDescription = trim((string) \App\Models\Setting::get('meta_description', 'Order food online with smooth delivery, pickup, and checkout experiences.'));
        $metaAuthor = trim((string) \App\Models\Setting::get('meta_author', 'WooFood'));
    @endphp
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $metaTitle)</title>
    <meta name="description" content="@yield('meta_description', $metaDescription)">
    <meta name="keywords" content="@yield('meta_keywords', $metaKeywords)">
    <meta name="author" content="@yield('meta_author', $metaAuthor)">
    <link rel="icon" href="{{ asset('public/' . \App\Models\Setting::get('favicon', 'assets/images/favicon.png')) }}"
        type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/bootstrap.css') }}">
    <link href="{{ asset('public/assets/fontawesome/css/fontawesome.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/fontawesome/css/brands.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/fontawesome/css/solid.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/fontawesome/css/sharp-thin.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/fontawesome/css/sharp-duotone-thin.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --portal-primary: #cb2b1d;
            --portal-secondary: #ffbf1f;
            --portal-accent: #111111;
            --portal-ink: #10151f;
            --portal-muted: #6b7280;
            --frontend-header-bg: {{ $frontendHeaderBgColor }};
            --frontend-header-text: {{ $frontendHeaderTextColor }};
            --frontend-footer-bg: {{ $frontendFooterBgColor }};
            --frontend-footer-text: {{ $frontendFooterTextColor }};
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Nunito Sans', sans-serif;
            color: var(--portal-ink);
            background:
                linear-gradient(180deg, rgba(9, 9, 11, 0.78), rgba(39, 39, 42, 0.38)),
                url('{{ $frontendBackgroundUrl }}') center/cover fixed no-repeat;
            overflow-x: hidden;
            width: 100%;
        }

        html {
            overflow-x: hidden;
            width: 100%;
            scrollbar-gutter: stable;
        }

        body.modal-open {
            padding-right: 0 !important;
        }

        .portal-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .portal-main {
            flex: 1 0 auto;
            position: relative;
        }

        .portal-nav {
            padding: 16px 0;
            background: var(--frontend-header-bg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--frontend-header-text);
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .frontend-logo-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .frontend-logo-wrap .frontend-logo-img {
            max-height: 46px;
            width: auto;
            display: block;
        }

        .frontend-logo-overlay-mask {
            position: absolute;
            inset: 0;
            background: var(--frontend-logo-color, transparent);
            -webkit-mask-image: var(--frontend-logo-url);
            mask-image: var(--frontend-logo-url);
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
            -webkit-mask-position: center;
            mask-position: center;
            -webkit-mask-size: contain;
            mask-size: contain;
            opacity: 0;
            pointer-events: none;
        }

        .frontend-logo-wrap.has-overlay .frontend-logo-img {
            opacity: 0;
        }

        .frontend-logo-wrap.has-overlay .frontend-logo-overlay-mask {
            opacity: 1;
        }

        .nav-links a {
            margin: 0 12px;
            font-size: 0.96rem;
            font-weight: 800;
            text-decoration: none;
            color: var(--frontend-header-text);
            opacity: 0.84;
            transition: color 0.2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--frontend-header-text);
            opacity: 1;
        }

        .portal-control {
            border: 0;
            background: transparent;
            color: var(--frontend-header-text);
            border-radius: 999px;
            padding: 0 38px 0 4px;
            min-height: 48px;
            font-size: 0.92rem;
            font-weight: 800;
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
            background-image: linear-gradient(45deg, transparent 50%, var(--frontend-header-text) 50%), linear-gradient(135deg, var(--frontend-header-text) 50%, transparent 50%);
            background-position: calc(100% - 20px) calc(50% - 3px), calc(100% - 14px) calc(50% - 3px);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
        }

        .portal-control:focus {
            outline: none;
        }

        .portal-control option {
            color: #172033;
            background: #ffffff;
        }

        .portal-control-shell {
            position: relative;
            min-width: 168px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.06));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 10px 24px rgba(0, 0, 0, 0.18);
            padding-left: 42px;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .portal-control-shell:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 191, 31, 0.34);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 14px 28px rgba(0, 0, 0, 0.22);
        }

        .portal-control-shell:focus-within {
            border-color: rgba(255, 191, 31, 0.55);
            box-shadow: 0 0 0 0.2rem rgba(255, 191, 31, 0.14), 0 14px 28px rgba(0, 0, 0, 0.22);
        }

        .portal-control-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: var(--frontend-header-text);
            opacity: 0.86;
            pointer-events: none;
        }

        .portal-cart {
            position: relative;
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--frontend-header-text);
            background: linear-gradient(135deg, rgba(255, 191, 31, 0.22), rgba(203, 43, 29, 0.22));
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .portal-cart-badge {
            position: absolute;
            top: -5px;
            right: -4px;
            min-width: 22px;
            height: 22px;
            border-radius: 999px;
            background: var(--portal-primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.76rem;
            font-weight: 800;
            padding: 0 6px;
        }

        .portal-footer {
            margin-top: 48px;
            padding: 48px 0 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--frontend-footer-text);
            font-size: 0.95rem;
            background: var(--frontend-footer-bg);
        }

        .portal-footer-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            color: var(--frontend-footer-text);
            text-decoration: none;
            transition: opacity 0.2s ease;
        }

        .portal-footer-brand:hover {
            opacity: 0.85;
            color: var(--frontend-footer-text);
        }

        .portal-footer-brand .frontend-logo-img {
            max-height: 42px;
        }

        .portal-footer a {
            text-decoration: none;
            color: var(--frontend-footer-text);
            font-weight: 700;
            opacity: 0.75;
            transition: opacity 0.2s ease, color 0.2s ease;
        }

        .portal-footer a:hover {
            opacity: 1;
            color: var(--portal-secondary);
        }

        .footer-bottom-bar {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.88rem;
            opacity: 0.72;
        }

        .footer-copyright {
            line-height: 1.6;
        }

        .footer-credit a {
            color: var(--portal-secondary) !important;
            opacity: 1 !important;
        }

        .footer-availability-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 18px;
        }

        .pickup-footer-panel {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
            gap: 18px;
            padding: 22px 24px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16);
        }

        .pickup-footer-kicker {
            font-size: 0.78rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-weight: 800;
            opacity: 0.72;
            margin-bottom: 8px;
        }

        .pickup-footer-title {
            margin: 0 0 8px;
            color: var(--frontend-footer-text);
            font-size: clamp(1.2rem, 2vw, 1.6rem);
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .pickup-footer-text {
            opacity: 0.82;
            line-height: 1.7;
        }

        .pickup-footer-slots {
            display: flex;
            flex-wrap: wrap;
            align-content: center;
            gap: 10px;
            justify-content: flex-end;
        }

        .pickup-footer-slot,
        .pickup-footer-holiday,
        .pickup-footer-empty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 800;
        }

        .pickup-footer-slot {
            background: linear-gradient(135deg, rgba(255, 191, 31, 0.94), rgba(255, 165, 0, 0.88));
            color: #111111;
        }

        .pickup-footer-holiday {
            background: rgba(255, 99, 71, 0.16);
            color: #ffd9cf;
            border: 1px solid rgba(255, 99, 71, 0.34);
        }

        .pickup-footer-empty {
            background: rgba(255, 255, 255, 0.08);
            color: var(--frontend-footer-text);
        }

        @media (max-width: 991.98px) {
            .nav-links {
                width: 100%;
                margin-top: 14px;
                text-align: left;
            }

            .nav-links a {
                display: block;
                margin: 0 0 10px;
            }

            .portal-control-shell {
                width: 100%;
            }

            .pickup-footer-panel {
                grid-template-columns: 1fr;
            }

            .pickup-footer-slots {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .frontend-logo-wrap .frontend-logo-img {
                max-height: 38px;
            }

            .portal-shell {
                overflow-x: hidden;
            }

            .portal-footer {
                padding: 40px 0 20px;
                text-align: center;
                width: 100%;
                overflow-x: hidden;
            }

            .portal-footer .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            .portal-footer .row {
                margin-left: 0;
                margin-right: 0;
            }

            .portal-footer .row > div {
                margin-bottom: 32px;
                padding-left: 0;
                padding-right: 0;
            }

            .portal-footer .row > div:last-child {
                margin-bottom: 0;
            }

            .portal-footer-brand {
                margin-right: 0 !important;
                margin-bottom: 24px !important;
                justify-content: center;
            }

            .portal-footer .d-flex.justify-content-between {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center;
                gap: 12px;
            }

            .pickup-footer-panel {
                padding: 20px 15px;
                text-align: center;
                margin-left: 0;
                margin-right: 0;
            }

            .pickup-footer-slots {
                justify-content: center !important;
                margin-top: 16px;
                gap: 8px;
            }

            .pickup-footer-slot {
                min-height: 38px;
                padding: 0 12px;
                font-size: 0.85rem;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <div class="portal-shell">
        @include('components.frontend.header', ['activeNav' => $activeNav ?? ''])

        <main class="portal-main">
            @yield('content')
        </main>

        @include('components.frontend.footer')
        @include('components.frontend.cookie-consent')
    </div>

    <script src="{{ asset('public/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>

</html>
