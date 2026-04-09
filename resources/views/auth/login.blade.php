<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $faviconPath = \App\Models\Setting::get('favicon', 'assets/images/favicon.png');
        $metaKeywords = \App\Models\Setting::get('meta_keywords', 'tickets, support, dashboard, reporting, operations');
        $metaDescription = \App\Models\Setting::get('meta_description', 'Ticket management system for activity tracking, team coordination, and reporting.');
        $metaAuthor = \App\Models\Setting::get('meta_author', 'Ticket Operations');
    @endphp
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="author" content="{{ $metaAuthor }}">
    <link rel="icon" href="{{ asset('public/' . $faviconPath) }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('public/' . $faviconPath) }}" type="image/x-icon">
    <title>Bazaar Bites Admin Login</title>

    <!-- Your existing styles + assets -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('public/assets/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/themify.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/flag-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/feather-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('public/assets/css/color-1.css') }}" media="screen">
    <link rel="stylesheet" href="{{ asset('public/assets/css/responsive.css') }}">

    <link href="{{ asset('public/assets/fontawesome/css/fontawesome.css')}}" rel="stylesheet" />
<link href="{{ asset('public/assets/fontawesome/css/brands.css')}}" rel="stylesheet" />
<link href="{{ asset('public/assets/fontawesome/css/solid.css')}}" rel="stylesheet" />
<link href="{{ asset('public/assets/fontawesome/css/sharp-thin.css')}}" rel="stylesheet" />
<link href="{{ asset('public/assets/fontawesome/css/sharp-duotone-thin.css')}}" rel="stylesheet" />
    @php
        $themePrimary = \App\Models\Setting::get('theme_primary', '#d56f3d');
        $themeSecondary = \App\Models\Setting::get('theme_secondary', '#1a1a1a');
        $themeAccent = \App\Models\Setting::get('theme_accent', '#ffbf2f');
        $loginHeadingColor = \App\Models\Setting::get('login_heading_color', '#ffffff');
        $loginOverlayColor = \App\Models\Setting::get('login_overlay_color', '#110c09');
        $loginBackgroundImage = \App\Models\Setting::get('login_bg_image', 'assets/images/login/bg.jpg');
        $loginOverlayRgb = '17, 12, 9';

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $loginOverlayColor)) {
            $normalizedOverlay = ltrim($loginOverlayColor, '#');
            if (strlen($normalizedOverlay) === 3) {
                $normalizedOverlay = collect(str_split($normalizedOverlay))->map(fn ($char) => $char . $char)->implode('');
            }

            $loginOverlayRgb = implode(', ', [
                hexdec(substr($normalizedOverlay, 0, 2)),
                hexdec(substr($normalizedOverlay, 2, 2)),
                hexdec(substr($normalizedOverlay, 4, 2)),
            ]);
        }
    @endphp
    <style>
        :root {
            --accent-1: {{ $themePrimary }};
            --accent-2: {{ $themeSecondary }};
            --accent-3: {{ $themeAccent }};
            --ink-1: #1f1a16;
            --ink-2: #6e6258;
        }

        body {
            background:
                radial-gradient(900px 500px at 10% 10%, rgba(213, 111, 61, 0.14), transparent 60%),
                radial-gradient(900px 500px at 90% 10%, rgba(255, 191, 47, 0.12), transparent 55%),
                linear-gradient(180deg, #fff7f0 0%, #f6eee7 100%);
            min-height: 100vh;
        }

        .login-shell {
            position: relative;
            overflow: hidden;
        }

        .floating-shape {
            position: absolute;
            border-radius: 999px;
            filter: blur(0.5px);
            opacity: 0.18;
            animation: floaty 10s ease-in-out infinite;
        }

        .shape-1 {
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, var(--accent-2), var(--accent-3));
            top: 6%;
            left: 8%;
        }

        .shape-2 {
            width: 240px;
            height: 240px;
            background: linear-gradient(135deg, var(--accent-1), #7ef9ff);
            bottom: 8%;
            right: 12%;
            animation-delay: -3s;
        }

        .shape-3 {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #f1c27d, #f5853f);
            top: 55%;
            left: 45%;
            animation-delay: -6s;
        }

        @keyframes floaty {
            0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); }
            50% { transform: translateY(-12px) translateX(8px) rotate(2deg); }
        }

        .login-hero {
            position: relative;
            height: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            background: linear-gradient(135deg, rgba(213, 111, 61, 0.12), rgba(26, 26, 26, 0.12));
        }

        .login-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: url("{{ asset('public/' . $loginBackgroundImage) }}") center/cover no-repeat;
            opacity: 0.22;
            mix-blend-mode: multiply;
        }

        .login-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba({{ $loginOverlayRgb }}, 0.80), rgba({{ $loginOverlayRgb }}, 0.45));
            z-index: 1;
        }

        .login-hero .hero-content {
            position: relative;
            z-index: 2;
            color: #fff;
            text-align: left;
            max-width: 520px;
            animation: fadeUp 0.8s ease both;
        }

        .login-hero h1 {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 12px;
            text-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
            color: {{ $loginHeadingColor }};
        }

        .login-hero p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 22px;
            text-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            font-size: 0.85rem;
            margin-bottom: 18px;
            color: #ffffff;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .login-card {
            position: relative;
            padding: 36px 34px;
            border-radius: 22px;
            background: rgba(255, 250, 245, 0.88);
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 60px rgba(48, 29, 16, 0.18);
            animation: fadeUp 0.7s ease both;
        }

        .login-main h4 {
            color: var(--ink-1);
            font-weight: 700;
        }

        .login-main p {
            color: var(--ink-2);
        }

        .theme-form .form-control {
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: none;
        }

        .theme-form .form-control:focus {
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(213, 111, 61, 0.15);
        }

        .btn-primary {
            border: none;
            border-radius: 14px;
            padding: 12px 16px;
            background: linear-gradient(90deg, var(--accent-1), #bf5126);
            box-shadow: 0 12px 24px rgba(213, 111, 61, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(191, 81, 38, 0.28);
            opacity: 0.95;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 1199px) {
            .login-hero {
                min-height: auto;
                padding: 50px 24px;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid login-shell">
        <span class="floating-shape shape-1"></span>
        <span class="floating-shape shape-2"></span>
        <span class="floating-shape shape-3"></span>
        <div class="row">
            <div class="col-xl-7 order-1">
                <div class="login-hero">
                    <div class="hero-content">
                        <div class="login-badge">
                            <i class="fa fa-store"></i>
                            <span>{{ \App\Models\Setting::get('login_badge_text', 'Bazaar Bites Shop') }}</span>
                        </div>
                        <h1>{{ \App\Models\Setting::get('login_heading', 'Fresh Flavors, Local Tastes') }}</h1>
                        <p>{{ \App\Models\Setting::get('login_description', 'Manage orders, products, delivery flow, and daily service from one polished foodshop dashboard.') }}</p>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="text-white-50">
                                <i class="fa fa-bag-shopping"></i> {{ \App\Models\Setting::get('login_bullet_1', 'Fast ordering and menu control') }}
                            </div>
                            <div class="text-white-50">
                                <i class="fa fa-chart-line"></i> {{ \App\Models\Setting::get('login_bullet_2', 'Clear daily sales insights') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5 p-0">
                <div class="login-card login-dark login-bg">
                    <div>

                        <div class="login-main">

                            <!-- Show session status / success messages (e.g. after password reset) -->
                            @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                            @endif

                            <!-- Show validation errors -->
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <form class="theme-form" method="POST" action="{{ route('admin.login.submit') }}">
                                @csrf
                                <div class="text-center mb-4"> {{-- Changed to text-center and added margin-bottom --}}
                                    <a class="logo d-block" href="{{ url('/') }}">
                                        {{-- Added d-block to ensure full width centering --}}
                                        <img class="img-fluid for-light"
                                            src="{{ asset('public/' . \App\Models\Setting::get('login_logo', 'assets/images/logo/logo.png')) }}"
                                            alt="logo" style="max-width: 18% !important; margin: 0 auto;">
                                        {{-- Added margin: 0 auto --}}

                                        <img class="img-fluid for-dark"
                                            src="{{ asset('public/' . \App\Models\Setting::get('login_logo', 'assets/images/logo/logo_dark.png')) }}"
                                            alt="logo dark" style="max-width: 18% !important; margin: 0 auto;">
                                    </a>
                                </div>
                                <h4>{{ __('Sign in to Admin Panel') }}</h4>
                                <p>{{ __('Enter your email and password to manage your portal.') }}</p>

                                <div class="form-group">
                                    <label class="col-form-label" for="login">Email Address</label>
                                    <input class="form-control @error('email') is-invalid @enderror" type="email"
                                        name="email" id="email" value="{{ old('email') }}" required autofocus
                                        autocomplete="email" placeholder="Test@gmail.com">

                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label" for="password">Password</label>
                                    <div class="form-input position-relative">
                                        <input class="form-control @error('password') is-invalid @enderror"
                                            type="password" name="password" id="password" required
                                            autocomplete="current-password" placeholder="*********">

                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="show-hide">
                                            <span class="show"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <!-- <div class="checkbox p-0">
                                        <input id="remember" type="checkbox" name="remember">
                                        <label class="text-muted" for="remember">Remember password</label>
                                    </div>

                                    @if (Route::has('password.request'))
                                        <a class="link" href="{{ route('password.request') }}">Forgot password?</a>
                                    @endif -->

                                    <div class="text-end mt-3">
                                        <button class="btn btn-primary btn-block w-100" type="submit">
                                            Login
                                        </button>
                                    </div>
                                </div>

                                <!-- Optional: public registration link -->
                                <!--
                                @if (Route::has('register'))
                                    <p class="mt-3 text-center">
                                        Don't have an account? <a href="{{ route('register') }}">Register</a>
                                    </p>
                                @endif
                                -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts (keep your existing ones) -->
    <script src="{{ asset('public/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('public/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('public/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <script src="{{ asset('public/assets/js/config.js') }}"></script>
    <script src="{{ asset('public/assets/js/script.js') }}"></script>

    <!-- Optional: Show/hide password toggle (if your template JS doesn't handle it) -->
    <script>
    document.querySelector('.show-hide .show').addEventListener('click', function() {
        let input = this.closest('.form-input').querySelector('input');
        if (input.type === 'password') {
            input.type = 'text';
            // this.textContent = 'Hide';
        } else {
            input.type = 'password';
            // this.textContent = 'Show';
        }
    });
    </script>
</body>

</html>
