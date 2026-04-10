<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        return view('setting.settings');
    }

    public function update(Request $request)
    {
        if (env('GUEST_MODE') === 'on') {
            return back()->with('error', __('settings_guest_mode_block'));
        }

        $timezones = \DateTimeZone::listIdentifiers();

        $data = $request->validate([
            'footer_text'    => 'nullable|string',
            'frontend_footer_address_line_1' => 'nullable|string|max:255',
            'frontend_footer_address_line_2' => 'nullable|string|max:255',
            'frontend_footer_phone' => 'nullable|string|max:80',
            'login_logo'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'admin_logo'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'frontend_header_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'frontend_footer_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'frontend_background_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'login_bg_image' => 'nullable|image|mimes:png,jpg,jpeg|max:4096',
            'favicon'        => 'nullable|mimes:png,ico|max:1024',
            'login_heading' => 'nullable|string|max:120',
            'login_badge_text' => 'nullable|string|max:120',
            'login_description' => 'nullable|string|max:500',
            'frontend_login_heading' => 'nullable|string|max:160',
            'frontend_login_description' => 'nullable|string|max:500',
            'login_bullet_1' => 'nullable|string|max:120',
            'login_bullet_2' => 'nullable|string|max:120',
            'login_heading_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'login_heading_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'login_overlay_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'login_overlay_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:10',
            'smtp_encryption' => 'nullable|in:ssl,tls',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_from_email' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'timezone' => ['nullable', 'string', Rule::in($timezones)],
            'sidebar_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_bg_start' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_bg_start_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_bg_end' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_bg_end_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_bg_start' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_bg_start_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_bg_end' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'header_bg_end_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_tab_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_tab_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_tab_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_tab_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'logo_overlay_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'logo_overlay_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_header_logo_overlay_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_header_logo_overlay_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_logo_overlay_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_logo_overlay_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_header_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_header_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_header_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_header_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_footer_bottom_text' => 'nullable|string|max:255',
            'frontend_collapse_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_collapse_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_collapse_heading_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_collapse_heading_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_product_title_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_product_title_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_product_price_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_product_price_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_product_description_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_product_description_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_choose_button_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_choose_button_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_choose_button_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_choose_button_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_view_cart_button_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_view_cart_button_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_view_cart_button_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_view_cart_button_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_checkout_button_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_checkout_button_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_checkout_button_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'frontend_checkout_button_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'map_provider' => 'nullable|in:leaflet,google',
            'google_maps_api_key' => 'nullable|string|max:255',
            'pickup_schedule_json' => 'nullable|string',
            'delivery_schedule_json' => 'nullable|string',
            'opening_hours_json' => 'nullable|string',
            'payment_methods_json' => 'nullable|string',
            'license_client_name' => 'nullable|string|max:255',
            'license_server_url' => 'nullable|string|max:255',
            'license_key' => 'nullable|string|max:255',
            'license_project_key' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:255',
            'meta_author' => 'nullable|string|max:120',
            'card_bg_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'card_bg_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'card_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'card_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'card_title' => 'nullable|string|max:60',
            'card_logo_source' => 'nullable|in:login,admin',
            'theme_primary' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'theme_primary_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'theme_secondary' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'theme_secondary_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'theme_accent' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'theme_accent_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_dashboard_text_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'sidebar_dashboard_text_color_text' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'cookie_banner_title' => 'nullable|string|max:160',
            'cookie_banner_description' => 'nullable|string|max:1200',
            'cookie_banner_notice' => 'nullable|string|max:1200',
            'cookie_accept_all_label' => 'nullable|string|max:80',
            'cookie_save_label' => 'nullable|string|max:80',
            'cookie_manage_label' => 'nullable|string|max:120',
            'cookie_back_label' => 'nullable|string|max:80',
            'cookie_show_details_label' => 'nullable|string|max:120',
            'cookie_hide_details_label' => 'nullable|string|max:120',
            'cookie_privacy_link_text' => 'nullable|string|max:80',
            'cookie_privacy_link_url' => 'nullable|string|max:255',
            'cookie_imprint_link_text' => 'nullable|string|max:80',
            'cookie_imprint_link_url' => 'nullable|string|max:255',
            'cookie_consent_storage_days' => 'nullable|integer|min:1|max:3650',
            'cookie_categories_json' => 'nullable|string',
        ]);

        $themePrimary = $request->input('theme_primary_text') ?: $request->input('theme_primary');
        $themeSecondary = $request->input('theme_secondary_text') ?: $request->input('theme_secondary');
        $themeAccent = $request->input('theme_accent_text') ?: $request->input('theme_accent');
        $sidebarDashboardText = $request->input('sidebar_dashboard_text_color_text') ?: $request->input('sidebar_dashboard_text_color');
        $cardTextColor = $request->input('card_text_color_text') ?: $request->input('card_text_color');
        $loginHeadingColor = $request->input('login_heading_color_text') ?: $request->input('login_heading_color');
        $loginOverlayColor = $request->input('login_overlay_color_text') ?: $request->input('login_overlay_color');
        $sidebarBg = $request->input('sidebar_bg_color_text') ?: $request->input('sidebar_bg_color');
        $headerBg = $request->input('header_bg_color_text') ?: $request->input('header_bg_color');
        $sidebarBgStart = $request->input('sidebar_bg_start_text') ?: $request->input('sidebar_bg_start');
        $sidebarBgEnd = $request->input('sidebar_bg_end_text') ?: $request->input('sidebar_bg_end');
        $headerBgStart = $request->input('header_bg_start_text') ?: $request->input('header_bg_start');
        $headerBgEnd = $request->input('header_bg_end_text') ?: $request->input('header_bg_end');
        $sidebarTabBg = $request->input('sidebar_tab_bg_color_text') ?: $request->input('sidebar_tab_bg_color');
        $sidebarTabText = $request->input('sidebar_tab_text_color_text') ?: $request->input('sidebar_tab_text_color');
        $logoOverlayColor = $request->input('logo_overlay_color_text') ?: $request->input('logo_overlay_color');
        $frontendHeaderLogoOverlayColor = trim((string) $request->input('frontend_header_logo_overlay_color_text', ''));
        $frontendFooterLogoOverlayColor = trim((string) $request->input('frontend_footer_logo_overlay_color_text', ''));
        $frontendColorFields = [
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
            'frontend_checkout_button_text_color',
        ];
        $pickupSchedule = $this->normalizePickupSchedule($request->input('pickup_schedule_json'));
        $deliverySchedule = $this->normalizeDeliverySchedule($request->input('delivery_schedule_json'));
        $openingHours = $this->normalizeOpeningHours($request->input('opening_hours_json'));
        $paymentMethods = $this->normalizePaymentMethods($request->input('payment_methods_json'));

        $cardBg = $request->input('card_bg_color_text') ?: $request->input('card_bg_color');

        unset(
            $data['theme_primary_text'],
            $data['theme_secondary_text'],
            $data['theme_accent_text'],
            $data['card_bg_color_text'],
            $data['sidebar_dashboard_text_color_text'],
            $data['card_text_color_text'],
            $data['login_heading_color_text'],
            $data['login_overlay_color_text'],
            $data['sidebar_bg_color_text'],
            $data['header_bg_color_text'],
            $data['sidebar_bg_start_text'],
            $data['sidebar_bg_end_text'],
            $data['header_bg_start_text'],
            $data['header_bg_end_text'],
            $data['sidebar_tab_bg_color_text'],
            $data['sidebar_tab_text_color_text'],
            $data['logo_overlay_color_text'],
            $data['frontend_header_logo_overlay_color_text'],
            $data['frontend_footer_logo_overlay_color_text'],
            $data['frontend_header_bg_color_text'],
            $data['frontend_header_text_color_text'],
            $data['frontend_footer_bg_color_text'],
            $data['frontend_footer_text_color_text'],
            $data['frontend_collapse_bg_color_text'],
            $data['frontend_collapse_heading_text_color_text'],
            $data['frontend_product_title_color_text'],
            $data['frontend_product_price_color_text'],
            $data['frontend_product_description_color_text'],
            $data['frontend_choose_button_color_text'],
            $data['frontend_choose_button_text_color_text'],
            $data['frontend_view_cart_button_color_text'],
            $data['frontend_view_cart_button_text_color_text'],
            $data['frontend_checkout_button_color_text'],
            $data['frontend_checkout_button_text_color_text']
        );

        if ($themePrimary) {
            $data['theme_primary'] = $themePrimary;
        }
        if ($themeSecondary) {
            $data['theme_secondary'] = $themeSecondary;
        }
        if ($themeAccent) {
            $data['theme_accent'] = $themeAccent;
        }
        if ($sidebarDashboardText) {
            $data['sidebar_dashboard_text_color'] = $sidebarDashboardText;
        }
        if ($cardTextColor) {
            $data['card_text_color'] = $cardTextColor;
        }
        if ($loginHeadingColor) {
            $data['login_heading_color'] = $loginHeadingColor;
        }
        if ($loginOverlayColor) {
            $data['login_overlay_color'] = $loginOverlayColor;
        }
        if ($sidebarBg) {
            $data['sidebar_bg_color'] = $sidebarBg;
        }
        if ($headerBg) {
            $data['header_bg_color'] = $headerBg;
        }
        if ($sidebarBgStart) {
            $data['sidebar_bg_start'] = $sidebarBgStart;
        }
        if ($sidebarBgEnd) {
            $data['sidebar_bg_end'] = $sidebarBgEnd;
        }
        if ($headerBgStart) {
            $data['header_bg_start'] = $headerBgStart;
        }
        if ($headerBgEnd) {
            $data['header_bg_end'] = $headerBgEnd;
        }
        if ($sidebarTabBg) {
            $data['sidebar_tab_bg_color'] = $sidebarTabBg;
        }
        if ($sidebarTabText) {
            $data['sidebar_tab_text_color'] = $sidebarTabText;
        }
        if (array_key_exists('logo_overlay_color', $data) || array_key_exists('logo_overlay_color_text', $data)) {
            $data['logo_overlay_color'] = $logoOverlayColor ?: '';
        }
        if (array_key_exists('frontend_header_logo_overlay_color', $data) || array_key_exists('frontend_header_logo_overlay_color_text', $data)) {
            $data['frontend_header_logo_overlay_color'] = $frontendHeaderLogoOverlayColor !== '' ? $frontendHeaderLogoOverlayColor : null;
        }
        if (array_key_exists('frontend_footer_logo_overlay_color', $data) || array_key_exists('frontend_footer_logo_overlay_color_text', $data)) {
            $data['frontend_footer_logo_overlay_color'] = $frontendFooterLogoOverlayColor !== '' ? $frontendFooterLogoOverlayColor : null;
        }
        foreach ($frontendColorFields as $field) {
            $resolvedColor = $request->input($field . '_text') ?: $request->input($field);
            if ($resolvedColor) {
                $data[$field] = $resolvedColor;
            }
        }
        if ($cardBg) {
            $data['card_bg_color'] = $cardBg;
        }
        $data['cookie_banner_enabled'] = $request->boolean('cookie_banner_enabled') ? '1' : '0';
        $data['cookie_categories_json'] = json_encode($this->normalizeCookieCategories($request->input('cookie_categories_json')));
        $data['pickup_schedule_json'] = json_encode($pickupSchedule);
        $data['delivery_schedule_json'] = json_encode($deliverySchedule);
        $data['opening_hours_json'] = json_encode($openingHours);
        $data['payment_methods_json'] = json_encode($paymentMethods);

        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                // Delete old file if exists
                $oldPath = Setting::get($key);
                if ($oldPath) {@unlink(public_path($oldPath));}

                // Store new file
                $file     = $request->file($key);
                $fileName = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/settings'), $fileName);
                $value = 'uploads/settings/' . $fileName;
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', __('Settings updated successfully!'));
    }

    private function normalizePickupSchedule(?string $payload): array
    {
        return $this->normalizeWeeklySchedule($payload);
    }

    private function normalizeDeliverySchedule(?string $payload): array
    {
        return $this->normalizeWeeklySchedule($payload);
    }

    private function normalizeOpeningHours(?string $payload): array
    {
        return $this->normalizeWeeklySchedule($payload);
    }

    private function normalizeWeeklySchedule(?string $payload): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $decoded = json_decode($payload ?: '{}', true);
        if (! is_array($decoded)) {
            $decoded = [];
        }

        $schedule = [];
        foreach ($days as $day) {
            $dayData = is_array($decoded[$day] ?? null) ? $decoded[$day] : [];
            $isHoliday = (bool) ($dayData['holiday'] ?? false);
            $times = collect($dayData['times'] ?? [])
                ->filter(fn ($time) => is_string($time) && preg_match('/^\d{2}:\d{2}$/', $time))
                ->map(fn ($time) => substr($time, 0, 5))
                ->unique()
                ->values()
                ->all();

            $schedule[$day] = [
                'holiday' => $isHoliday,
                'times' => $isHoliday ? [] : $times,
            ];
        }

        return $schedule;
    }

    private function normalizePaymentMethods(?string $payload): array
    {
        $decoded = json_decode($payload ?: '[]', true);
        if (! is_array($decoded)) {
            $decoded = [];
        }

        $allowedTypes = ['stripe', 'cash_on_delivery', 'bank_account', 'custom'];
        $methods = [];

        foreach ($decoded as $method) {
            if (! is_array($method)) {
                continue;
            }

            $title = trim((string) ($method['title'] ?? ''));
            $code = $this->slugifyPaymentMethodCode((string) ($method['code'] ?? $title));
            $type = in_array(($method['type'] ?? ''), $allowedTypes, true) ? $method['type'] : 'custom';
            $config = is_array($method['config'] ?? null) ? $method['config'] : [];

            if ($title === '' && $code === '') {
                continue;
            }

            $normalized = [
                'id' => trim((string) ($method['id'] ?? uniqid('pm_', true))),
                'title' => $title !== '' ? $title : ucfirst(str_replace('_', ' ', $code ?: $type)),
                'code' => $code !== '' ? $code : $this->slugifyPaymentMethodCode($title ?: $type),
                'type' => $type,
                'description' => trim((string) ($method['description'] ?? '')),
                'is_active' => (bool) ($method['is_active'] ?? false),
                'config' => [],
            ];

            if ($type === 'stripe') {
                $environment = ($config['environment'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';
                $normalized['config'] = [
                    'environment' => $environment,
                    'live_public_key' => trim((string) ($config['live_public_key'] ?? '')),
                    'live_secret_key' => trim((string) ($config['live_secret_key'] ?? '')),
                    'sandbox_public_key' => trim((string) ($config['sandbox_public_key'] ?? '')),
                    'sandbox_secret_key' => trim((string) ($config['sandbox_secret_key'] ?? '')),
                ];
            } elseif ($type === 'bank_account') {
                $normalized['config'] = [
                    'account_title' => trim((string) ($config['account_title'] ?? '')),
                    'iban' => trim((string) ($config['iban'] ?? '')),
                    'branch_name' => trim((string) ($config['branch_name'] ?? '')),
                    'account_number' => trim((string) ($config['account_number'] ?? '')),
                ];
            } elseif ($type === 'custom') {
                $environment = ($config['environment'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';
                $normalized['config'] = [
                    'environment' => $environment,
                    'live_public_key' => trim((string) ($config['live_public_key'] ?? '')),
                    'live_private_key' => trim((string) ($config['live_private_key'] ?? '')),
                    'sandbox_public_key' => trim((string) ($config['sandbox_public_key'] ?? '')),
                    'sandbox_private_key' => trim((string) ($config['sandbox_private_key'] ?? '')),
                ];
            }

            $methods[] = $normalized;
        }

        return array_values($methods);
    }

    private function slugifyPaymentMethodCode(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?: '';

        return trim($value, '_');
    }

    private function normalizeCookieCategories(?string $payload): array
    {
        $decoded = json_decode($payload ?: '[]', true);
        if (! is_array($decoded)) {
            $decoded = [];
        }

        $categories = [];

        foreach ($decoded as $index => $category) {
            if (! is_array($category)) {
                continue;
            }

            $title = trim((string) ($category['title'] ?? ''));
            $key = trim((string) ($category['key'] ?? ''));
            $normalizedKey = preg_replace('/[^a-z0-9_]+/', '_', strtolower($key !== '' ? $key : $title));
            $normalizedKey = trim((string) $normalizedKey, '_');

            if ($title === '' && $normalizedKey === '') {
                continue;
            }

            $details = collect($category['details'] ?? [])
                ->filter(fn ($detail) => is_array($detail))
                ->map(function ($detail) {
                    return [
                        'label' => trim((string) ($detail['label'] ?? '')),
                        'value' => trim((string) ($detail['value'] ?? '')),
                        'link_text' => trim((string) ($detail['link_text'] ?? '')),
                        'link_url' => trim((string) ($detail['link_url'] ?? '')),
                    ];
                })
                ->filter(fn ($detail) => $detail['label'] !== '' || $detail['value'] !== '' || $detail['link_text'] !== '' || $detail['link_url'] !== '')
                ->values()
                ->all();

            $isEssential = (bool) ($category['is_essential'] ?? false);

            $categories[] = [
                'id' => trim((string) ($category['id'] ?? ('cookie_' . ($index + 1)))),
                'key' => $normalizedKey !== '' ? $normalizedKey : 'cookie_' . ($index + 1),
                'title' => $title !== '' ? $title : ucfirst(str_replace('_', ' ', $normalizedKey ?: ('cookie_' . ($index + 1)))),
                'description' => trim((string) ($category['description'] ?? '')),
                'is_essential' => $isEssential,
                'default_selected' => $isEssential || (bool) ($category['default_selected'] ?? false),
                'details' => $details,
            ];
        }

        return array_values($categories);
    }

    public function updateLicense(Request $request)
    {
        if (env('GUEST_MODE') === 'on') {
            return back()->with('error', __('settings_guest_mode_block'));
        }

        $data = $request->validate([
            'license_client_name' => 'nullable|string|max:255',
            'license_server_url' => 'nullable|string|max:255',
            'license_key' => 'nullable|string|max:255',
            'license_project_key' => 'nullable|string|max:255',
            'license_project_secret' => 'nullable|string|max:255',
        ]);

        foreach ([
            'license_client_name',
            'license_server_url',
            'license_key',
            'license_project_key',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $data[$key]]);
            }
        }

        if (! empty($data['license_project_secret'])) {
            Storage::disk('local')->put('dummyfile.txt', trim($data['license_project_secret']));
        }

        return back()->with('success', __('Settings updated successfully!'));
    }
}
