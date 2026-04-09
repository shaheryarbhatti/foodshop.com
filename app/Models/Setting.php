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
}
