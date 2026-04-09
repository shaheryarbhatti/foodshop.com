@extends('layouts.frontend')

@php
    $activeNav = 'submit-ticket';
    $themePrimary = \App\Models\Setting::get('theme_primary', '#d56f3d');
    $themeSecondary = \App\Models\Setting::get('theme_secondary', '#1a1a1a');
    $loginOverlayColor = \App\Models\Setting::get('login_overlay_color', '#110c09');
    $loginBackgroundImage = \App\Models\Setting::get('login_bg_image', 'assets/images/login/bg.jpg');
    $frontendLoginHeading = \App\Models\Setting::get('frontend_login_heading', 'Welcome back to Bazaar Bites');
    $frontendLoginDescription = \App\Models\Setting::get('frontend_login_description', 'Log in to continue ordering, review your basket, and move from craving to checkout in just a few taps.');
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

@section('title', 'Portal Login')

@section('styles')
    <style>
        body {
            background: linear-gradient(180deg, #fff7f0 0%, #f7ede3 100%);
        }

        .portal-main {
            display: flex;
            flex-direction: column;
        }

        .login-hero {
            position: relative;
            min-height: 320px;
            padding: 48px 0 140px;
            overflow: hidden;
            background:
                linear-gradient(90deg, rgba({{ $loginOverlayRgb }}, 0.92) 0%, rgba({{ $loginOverlayRgb }}, 0.74) 46%, rgba({{ $loginOverlayRgb }}, 0.18) 100%),
                url('{{ asset('public/' . $loginBackgroundImage) }}') center/cover no-repeat;
        }

        .login-hero::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 110px;
            background: linear-gradient(180deg, rgba(246, 250, 252, 0), rgba(246, 250, 252, 0.98));
        }

        .hero-copy {
            position: relative;
            z-index: 1;
            color: #ffffff;
        }

        .hero-copy h1 {
            max-width: 520px;
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1.02;
            font-weight: 900;
            letter-spacing: -0.03em;
            text-shadow: 0 10px 22px rgba(0, 0, 0, 0.18);
        }

        .hero-copy p {
            max-width: 520px;
            margin: 14px 0 0;
            font-size: 1.08rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }

        .login-panel-wrap {
            position: relative;
            z-index: 2;
            margin-top: -110px;
            padding-bottom: 24px;
        }

        .login-card {
            max-width: 460px;
            margin: 0 auto;
            padding: 28px 28px 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(18, 42, 61, 0.08);
            box-shadow: 0 24px 54px rgba(18, 42, 61, 0.14);
        }

        .login-lock {
            width: 68px;
            height: 68px;
            margin: 0 auto 18px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #d56f3d;
            background: linear-gradient(180deg, rgba(213, 111, 61, 0.18), rgba(255, 191, 47, 0.12));
        }

        .form-label {
            font-weight: 700;
            color: #2c211a;
        }

        .form-control {
            min-height: 50px;
            border-radius: 12px;
            border: 1px solid rgba(44, 33, 26, 0.12);
            box-shadow: none;
        }

        .form-control:focus {
            border-color: rgba(213, 111, 61, 0.7);
            box-shadow: 0 0 0 3px rgba(213, 111, 61, 0.12);
        }

        .portal-submit {
            width: 100%;
            min-height: 50px;
            border: 0;
            border-radius: 999px;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(90deg, {{ $themePrimary }}, {{ $themePrimary }});
            box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.08), 0 10px 18px rgba(0, 0, 0, 0.14);
        }

        .portal-submit:hover {
            background: linear-gradient(90deg, {{ $themeSecondary }}, {{ $themeSecondary }});
        }

        .back-home {
            margin-top: 18px;
            text-align: center;
        }

        .back-home a {
            color: #8d411e;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 767.98px) {
            .login-hero {
                min-height: 280px;
                padding: 34px 0 124px;
                background-position: center right;
            }

            .login-panel-wrap {
                margin-top: -90px;
            }

            .login-card {
                padding: 22px 18px 18px;
                border-radius: 18px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="login-hero">
        <div class="container">
            <div class="hero-copy">
                <h1>{{ $frontendLoginHeading }}</h1>
                <p>{{ $frontendLoginDescription }}</p>
            </div>
        </div>
    </section>

    <section class="login-panel-wrap">
        <div class="container">
            <div class="login-card">
                <div class="login-lock">
                    <i class="fa-solid fa-lock"></i>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('frontend.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="********" required>
                    </div>

                    <button type="submit" class="portal-submit">Login</button>
                </form>

                <div class="back-home">
                    <a href="{{ route('frontend.register') }}">{{ __("Don't have an account? Register now") }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
