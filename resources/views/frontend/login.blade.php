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
        :root {
            --login-accent: {{ $themePrimary }};
            --login-accent-deep: {{ $themeSecondary }};
            --login-ink: #1f2937;
            --login-muted: #6b7280;
            --login-panel: rgba(255, 255, 255, 0.94);
            --login-border: rgba(148, 163, 184, 0.22);
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(255, 214, 184, 0.72), transparent 34%),
                radial-gradient(circle at bottom right, rgba(255, 204, 150, 0.4), transparent 30%),
                linear-gradient(180deg, #fff8f2 0%, #f4ecdf 100%);
        }

        .portal-main {
            display: flex;
            flex-direction: column;
        }

        .login-hero {
            position: relative;
            min-height: 360px;
            padding: 56px 0 152px;
            overflow: hidden;
            background:
                linear-gradient(90deg, rgba({{ $loginOverlayRgb }}, 0.92) 0%, rgba({{ $loginOverlayRgb }}, 0.74) 46%, rgba({{ $loginOverlayRgb }}, 0.18) 100%),
                url('{{ asset('public/' . $loginBackgroundImage) }}') center/cover no-repeat;
        }

        .login-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 18% 24%, rgba(255, 255, 255, 0.16), transparent 22%),
                radial-gradient(circle at 78% 16%, rgba(255, 255, 255, 0.12), transparent 18%);
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

        .hero-highlights {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .hero-pill {
            padding: 10px 16px;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.01em;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .login-panel-wrap {
            position: relative;
            z-index: 2;
            margin-top: -110px;
            padding-bottom: 24px;
        }

        .login-card {
            max-width: 560px;
            margin: 0 auto;
            padding: 18px;
            border-radius: 30px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.48);
            box-shadow: 0 28px 70px rgba(18, 42, 61, 0.16);
            backdrop-filter: blur(18px);
        }

        .login-shell {
            border-radius: 24px;
            background: var(--login-panel);
            border: 1px solid var(--login-border);
            padding: 24px;
        }

        .login-tab-bar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 20px;
            padding: 6px;
            border-radius: 18px;
            background: #f4f4f5;
        }

        .login-tab {
            border: 0;
            border-radius: 14px;
            padding: 14px 12px;
            background: transparent;
            color: var(--login-muted);
            text-align: left;
            transition: all 0.25s ease;
        }

        .login-tab strong,
        .login-tab span {
            display: block;
        }

        .login-tab strong {
            font-size: 0.98rem;
            font-weight: 800;
            color: var(--login-ink);
        }

        .login-tab span {
            margin-top: 4px;
            font-size: 0.77rem;
            line-height: 1.35;
        }

        .login-tab.active {
            background: linear-gradient(135deg, rgba(213, 111, 61, 0.12), rgba(255, 255, 255, 0.94));
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }

        .login-tab.active strong,
        .login-tab.active span {
            color: #7c2d12;
        }

        .login-pane {
            display: none;
        }

        .login-pane.active {
            display: block;
        }

        .login-pane-top {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 18px;
        }

        .login-lock {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--login-accent);
            background: linear-gradient(180deg, rgba(213, 111, 61, 0.18), rgba(255, 191, 47, 0.12));
            flex-shrink: 0;
        }

        .login-pane-copy h3 {
            margin: 2px 0 6px;
            font-size: 1.4rem;
            font-weight: 900;
            color: #111827;
        }

        .login-pane-copy p {
            margin: 0;
            color: var(--login-muted);
            line-height: 1.55;
        }

        .login-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #9a3412;
            background: rgba(254, 215, 170, 0.5);
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
            background: rgba(255, 255, 255, 0.95);
        }

        .form-control:focus {
            border-color: rgba(213, 111, 61, 0.7);
            box-shadow: 0 0 0 3px rgba(213, 111, 61, 0.12);
        }

        .login-note {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 0.9rem;
            color: #4b5563;
            background: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.18);
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

        .login-footer-links {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .login-footer-links a {
            color: #8d411e;
            font-weight: 800;
            text-decoration: none;
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
                min-height: 320px;
                padding: 34px 0 132px;
                background-position: center right;
            }

            .login-panel-wrap {
                margin-top: -90px;
            }

            .login-card {
                padding: 14px;
                border-radius: 22px;
            }

            .login-shell {
                padding: 18px;
            }

            .login-tab-bar {
                grid-template-columns: 1fr;
            }

            .login-pane-top {
                flex-direction: column;
            }

            .login-lock {
                width: 58px;
                height: 58px;
                border-radius: 18px;
                font-size: 1.4rem;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $activeLoginType = old('login_type', 'customer');
        $loginTabs = [
            'customer' => [
                'icon' => 'fa-user',
                'tab_title' => 'Customer',
                'tab_caption' => 'Track orders and checkout fast',
                'badge' => 'Customer access',
                'title' => 'Customer login',
                'description' => 'Use your existing customer account to continue ordering, view past purchases, and manage your delivery details.',
                'submit' => 'Login as Customer',
                'note' => 'New here? Create a customer account to start ordering online.',
                'show_register' => true,
            ],
            'staff' => [
                'icon' => 'fa-user-tie',
                'tab_title' => 'Staff',
                'tab_caption' => 'Staff portal for daily operations',
                'badge' => 'Staff portal',
                'title' => 'Staff login',
                'description' => 'Sign in with your staff account to access the operations dashboard, orders, products, and internal tools.',
                'submit' => 'Login as Staff',
                'note' => 'Only users assigned the Staff role can sign in through this tab.',
                'show_register' => false,
            ],
            'driver' => [
                'icon' => 'fa-motorcycle',
                'tab_title' => 'Driver',
                'tab_caption' => 'Delivery access for riders',
                'badge' => 'Driver portal',
                'title' => 'Driver login',
                'description' => 'Drivers can use this area to enter with their delivery account and reach the system using role-based access.',
                'submit' => 'Login as Driver',
                'note' => 'Only users assigned the driver role can sign in through this tab.',
                'show_register' => false,
            ],
        ];
    @endphp

    <section class="login-hero">
        <div class="container">
            <div class="hero-copy">
                <h1>{{ $frontendLoginHeading }}</h1>
                <p>{{ $frontendLoginDescription }}</p>
                <div class="hero-highlights">
                    <span class="hero-pill">Customer ordering</span>
                    <span class="hero-pill">Staff operations</span>
                    <span class="hero-pill">Driver access</span>
                </div>
            </div>
        </div>
    </section>

    <section class="login-panel-wrap">
        <div class="container">
            <div class="login-card">
                <div class="login-shell">
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

                    <div class="login-tab-bar" role="tablist" aria-label="Login types">
                        @foreach ($loginTabs as $type => $tab)
                            <button type="button"
                                class="login-tab {{ $activeLoginType === $type ? 'active' : '' }}"
                                data-login-tab="{{ $type }}"
                                role="tab"
                                aria-selected="{{ $activeLoginType === $type ? 'true' : 'false' }}">
                                <strong>{{ $tab['tab_title'] }}</strong>
                                <span>{{ $tab['tab_caption'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    @foreach ($loginTabs as $type => $tab)
                        <div class="login-pane {{ $activeLoginType === $type ? 'active' : '' }}"
                            data-login-pane="{{ $type }}">
                            <div class="login-pane-top">
                                <div class="login-lock">
                                    <i class="fa-solid {{ $tab['icon'] }}"></i>
                                </div>
                                <div class="login-pane-copy">
                                    <span class="login-type-badge">{{ $tab['badge'] }}</span>
                                    <h3>{{ $tab['title'] }}</h3>
                                    <p>{{ $tab['description'] }}</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('frontend.login.submit') }}">
                                @csrf
                                <input type="hidden" name="login_type" value="{{ $type }}">

                                <div class="mb-3">
                                    <label for="email-{{ $type }}" class="form-label">Email Address</label>
                                    <input type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        id="email-{{ $type }}"
                                        name="email"
                                        value="{{ $activeLoginType === $type ? old('email') : '' }}"
                                        placeholder="name@example.com"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="password-{{ $type }}" class="form-label">Password</label>
                                    <input type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        id="password-{{ $type }}"
                                        name="password"
                                        placeholder="********"
                                        required>
                                </div>

                                <button type="submit" class="portal-submit">{{ $tab['submit'] }}</button>
                            </form>

                            <div class="login-note">{{ $tab['note'] }}</div>

                            <div class="login-footer-links" style="text-align: center;">
                                @if ($tab['show_register'])
                                    <a href="{{ route('frontend.register') }}">{{ __("Don't have an account? Register now") }}</a>
                                @else
                                    <!-- <a href="{{ route('admin.login') }}">Need the admin login page?</a> -->
                                @endif
                                <!-- <a href="{{ route('frontend.home') }}">Back to homepage</a> -->
                            </div>
                        </div>
                    @endforeach

                    <!-- <div class="back-home">
                        <a href="{{ route('admin.login') }}">Open admin portal instead</a>
                    </div> -->
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('[data-login-tab]');
            const panes = document.querySelectorAll('[data-login-pane]');

            const activate = (target) => {
                tabs.forEach((tab) => {
                    const isActive = tab.dataset.loginTab === target;
                    tab.classList.toggle('active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panes.forEach((pane) => {
                    pane.classList.toggle('active', pane.dataset.loginPane === target);
                });
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', function () {
                    activate(this.dataset.loginTab);
                });
            });
        });
    </script>
@endsection
