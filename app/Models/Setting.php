<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // Helper to get setting value easily
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function getMany(array $defaults = []): array
    {
        if (empty($defaults)) {
            return [];
        }

        $settings = self::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        return collect($defaults)
            ->mapWithKeys(fn ($default, $key) => [$key => $settings[$key] ?? $default])
            ->all();
    }

    public static function paymentMethods(bool $activeOnly = false): array
    {
        $methods = json_decode(self::get('payment_methods_json', '[]'), true);
        if (! is_array($methods)) {
            $methods = [];
        }

        $methods = collect($methods)
            ->filter(fn ($method) => is_array($method))
            ->values()
            ->all();

        if ($activeOnly) {
            $methods = array_values(array_filter($methods, fn ($method) => ! empty($method['is_active'])));
        }

        return $methods;
    }

    public static function cookieCategories(): array
    {
        $categories = json_decode(self::get('cookie_categories_json', '[]'), true);
        if (! is_array($categories) || empty($categories)) {
            $categories = [
                [
                    'id' => 'essential',
                    'key' => 'essential',
                    'title' => 'Essential',
                    'description' => 'Essential cookies enable basic functions and are necessary for the proper functioning of the website.',
                    'is_essential' => true,
                    'default_selected' => true,
                    'details' => [
                        [
                            'label' => 'Cookie Name',
                            'value' => 'site_cookie_consent',
                            'link_text' => '',
                            'link_url' => '',
                        ],
                    ],
                ],
                [
                    'id' => 'external_media',
                    'key' => 'external_media',
                    'title' => 'External Media',
                    'description' => 'Content from video and social media platforms is blocked by default until the visitor allows it.',
                    'is_essential' => false,
                    'default_selected' => false,
                    'details' => [],
                ],
            ];
        }

        return collect($categories)
            ->filter(fn ($category) => is_array($category))
            ->map(function ($category, $index) {
                $details = collect($category['details'] ?? [])
                    ->filter(fn ($detail) => is_array($detail))
                    ->map(fn ($detail) => [
                        'label' => trim((string) ($detail['label'] ?? '')),
                        'value' => trim((string) ($detail['value'] ?? '')),
                        'link_text' => trim((string) ($detail['link_text'] ?? '')),
                        'link_url' => trim((string) ($detail['link_url'] ?? '')),
                    ])
                    ->filter(fn ($detail) => $detail['label'] !== '' || $detail['value'] !== '' || $detail['link_text'] !== '' || $detail['link_url'] !== '')
                    ->values()
                    ->all();

                $key = trim((string) ($category['key'] ?? ''));

                return [
                    'id' => trim((string) ($category['id'] ?? ('cookie_' . ($index + 1)))),
                    'key' => $key !== '' ? $key : 'cookie_' . ($index + 1),
                    'title' => trim((string) ($category['title'] ?? 'Cookie Category')),
                    'description' => trim((string) ($category['description'] ?? '')),
                    'is_essential' => (bool) ($category['is_essential'] ?? false),
                    'default_selected' => (bool) ($category['is_essential'] ?? false) || (bool) ($category['default_selected'] ?? false),
                    'details' => $details,
                ];
            })
            ->values()
            ->all();
    }

    public static function cookieBannerConfig(): array
    {
        return [
            'enabled' => filter_var(self::get('cookie_banner_enabled', '1'), FILTER_VALIDATE_BOOL),
            'title' => self::get('cookie_banner_title', 'Privacy settings'),
            'description' => self::get('cookie_banner_description', 'We use cookies and other technologies on our website. Some are essential, while others help us improve this website and your experience.'),
            'notice' => self::get('cookie_banner_notice', 'Personal data may be processed for personalization, content measurement, and consent storage.'),
            'accept_all_label' => self::get('cookie_accept_all_label', 'Accept all'),
            'save_label' => self::get('cookie_save_label', 'Save'),
            'manage_label' => self::get('cookie_manage_label', 'Individual Privacy Settings'),
            'back_label' => self::get('cookie_back_label', 'Back'),
            'show_details_label' => self::get('cookie_show_details_label', 'Show cookie information'),
            'hide_details_label' => self::get('cookie_hide_details_label', 'Hide cookie information'),
            'privacy_link_text' => self::get('cookie_privacy_link_text', 'Privacy Policy'),
            'privacy_link_url' => self::get('cookie_privacy_link_url', ''),
            'imprint_link_text' => self::get('cookie_imprint_link_text', 'Imprint'),
            'imprint_link_url' => self::get('cookie_imprint_link_url', ''),
            'consent_days' => max(1, (int) self::get('cookie_consent_storage_days', '365')),
            'categories' => self::cookieCategories(),
        ];
    }
}
