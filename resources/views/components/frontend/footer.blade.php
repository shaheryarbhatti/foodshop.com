@php
    $frontendFooterLogo = \App\Models\Setting::get('frontend_footer_logo', 'assets/images/logo/logo_dark.png');
    $frontendFooterLogoUrl = asset('public/' . $frontendFooterLogo);
    $frontendFooterOverlayColor = \App\Models\Setting::get('frontend_footer_logo_overlay_color', '');
    $frontendFooterOverlayClass = $frontendFooterOverlayColor ? 'has-overlay' : '';
    $footerText = \App\Models\Setting::get('footer_text', 'Online delivery platform for modern food ordering.');
    $footerBottomText = \App\Models\Setting::get('frontend_footer_bottom_text', '&copy; ' . date('Y') . ' WooFood. ' . __('frontend_all_rights_reserved'));
    $pickupSchedule = json_decode(\App\Models\Setting::get('pickup_schedule_json', '{}'), true);
    $pickupDayKey = strtolower(now()->englishDayOfWeek);
    $pickupLabels = [
        'monday' => __('pickup_day_monday'),
        'tuesday' => __('pickup_day_tuesday'),
        'wednesday' => __('pickup_day_wednesday'),
        'thursday' => __('pickup_day_thursday'),
        'friday' => __('pickup_day_friday'),
        'saturday' => __('pickup_day_saturday'),
        'sunday' => __('pickup_day_sunday'),
    ];
    $todayPickup = is_array($pickupSchedule[$pickupDayKey] ?? null) ? $pickupSchedule[$pickupDayKey] : ['holiday' => false, 'times' => []];
    $todayPickupTimes = collect($todayPickup['times'] ?? [])->filter()->values()->all();
    $todayPickupRanges = [];
    for ($i = 0; $i < count($todayPickupTimes); $i += 2) {
        $startTime = $todayPickupTimes[$i] ?? null;
        $endTime = $todayPickupTimes[$i + 1] ?? null;
        if (! $startTime) {
            continue;
        }

        $startLabel = \Carbon\Carbon::createFromFormat('H:i', $startTime)->format('g:i A');
        $endLabel = $endTime ? \Carbon\Carbon::createFromFormat('H:i', $endTime)->format('g:i A') : null;

        $todayPickupRanges[] = $endLabel
            ? $startLabel . ' ' . __('pickup_time_separator') . ' ' . $endLabel
            : $startLabel;
    }
    $todayPickupHoliday = (bool) ($todayPickup['holiday'] ?? false);
@endphp
<footer class="portal-footer">
    <div class="container">
        <div class="pickup-footer-panel mb-4">
            <div class="pickup-footer-copy">
                <div class="pickup-footer-kicker">{{ __('pickup_today') }}</div>
                <h4 class="pickup-footer-title">{{ __('pickup_available_for', ['day' => $pickupLabels[$pickupDayKey] ?? ucfirst($pickupDayKey)]) }}</h4>
                <p class="pickup-footer-text mb-0">
                    {{ $todayPickupHoliday ? __('pickup_today_holiday_text') : __('pickup_today_available_text') }}
                </p>
            </div>
            <div class="pickup-footer-slots">
                @if($todayPickupHoliday)
                    <span class="pickup-footer-holiday">{{ __('pickup_holiday_active') }}</span>
                @elseif(! empty($todayPickupRanges))
                    @foreach($todayPickupRanges as $pickupRange)
                        <span class="pickup-footer-slot">{{ $pickupRange }}</span>
                    @endforeach
                @else
                    <span class="pickup-footer-empty">{{ __('pickup_no_slots_today') }}</span>
                @endif
            </div>
        </div>
        <div class="row g-4 align-items-start">
            <div class="col-lg-3 col-md-6">
                <a class="portal-footer-brand mb-3 me-4" href="{{ route('frontend.home') }}">
                    <span class="frontend-logo-wrap {{ $frontendFooterOverlayClass }}"
                        style="--frontend-logo-color: {{ $frontendFooterOverlayColor ?: 'transparent' }}; --frontend-logo-url: url('{{ $frontendFooterLogoUrl }}');">
                        <img class="frontend-logo-img" src="{{ $frontendFooterLogoUrl }}" alt="Frontend Footer Logo">
                        <span class="frontend-logo-overlay-mask" aria-hidden="true"></span>
                    </span>
                </a>
                <p class="mt-3 mb-0" style="line-height:1.7;">{{ $footerText }}</p>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold text-white mb-3">{{ __('frontend_footer_address') }}</h5>
                <p class="mb-2">87 Lexington Street</p>
                <p class="mb-2">15534 New York</p>
                <p class="mb-0">support@woofood.local</p>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold text-white mb-3">{{ __('frontend_footer_account') }}</h5>
                <a href="{{ route('login') }}" class="d-block mb-2">{{ __('frontend_login_register') }}</a>
                <a href="{{ route('frontend.my-tickets') }}" class="d-block mb-2">{{ __('frontend_track_order') }}</a>
                <a href="{{ route('admin.login') }}" class="d-block">{{ __('frontend_admin_panel') }}</a>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold text-white mb-3">{{ __('frontend_footer_links') }}</h5>
                <a href="{{ route('frontend.home') }}" class="d-block mb-2">{{ __('frontend_shipping_costs') }}</a>
                <a href="{{ route('frontend.home') }}" class="d-block mb-2">{{ __('frontend_payment_methods') }}</a>
                <a href="{{ route('frontend.home') }}" class="d-block">{{ __('frontend_cancellation_policy') }}</a>
            </div>
        </div>
        <div class="footer-bottom-bar d-flex flex-column flex-md-row justify-content-between align-items-md-center pt-4 mt-4">
            <div class="footer-copyright mb-2 mb-md-0">{!! $footerBottomText !!}</div>
            <div class="footer-credit">{{ __('frontend_designed_by') }} <a target="_blank" href="https://www.linkedin.com/in/shaheryar-bhatti-383553110/">Shaheryar Bhatti</a></div>
        </div>
    </div>
</footer>
