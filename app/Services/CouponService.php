<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Str;

class CouponService
{
    public function resolve(?string $code, float $subtotal): array
    {
        $normalizedCode = Str::upper(trim((string) $code));
        if ($normalizedCode === '') {
            return $this->emptyResult();
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalizedCode])
            ->first();

        if (! $coupon) {
            return $this->invalidResult(__('coupon_code_invalid'));
        }

        if (! $coupon->status) {
            return $this->invalidResult(__('coupon_code_inactive'));
        }

        $now = now();
        if ($coupon->starts_at && $coupon->starts_at->gt($now)) {
            return $this->invalidResult(__('coupon_code_not_started'));
        }

        if ($coupon->expires_at && $coupon->expires_at->lt($now)) {
            return $this->invalidResult(__('coupon_code_expired'));
        }

        if ((float) $coupon->minimum_order_amount > 0 && $subtotal < (float) $coupon->minimum_order_amount) {
            return $this->invalidResult(__('coupon_code_minimum_not_met', [
                'amount' => number_format((float) $coupon->minimum_order_amount, 2),
            ]));
        }

        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            return $this->invalidResult(__('coupon_code_usage_limit_reached'));
        }

        $discountAmount = $coupon->discount_type === 'percentage'
            ? $subtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        if ($coupon->maximum_discount_amount !== null && $discountAmount > (float) $coupon->maximum_discount_amount) {
            $discountAmount = (float) $coupon->maximum_discount_amount;
        }

        $discountAmount = min(max($discountAmount, 0), max($subtotal, 0));

        return [
            'valid' => true,
            'message' => __('coupon_code_applied_successfully'),
            'coupon' => $coupon,
            'code' => $coupon->code,
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    public function emptyResult(): array
    {
        return [
            'valid' => false,
            'message' => null,
            'coupon' => null,
            'code' => null,
            'discount_amount' => 0,
        ];
    }

    private function invalidResult(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'coupon' => null,
            'code' => null,
            'discount_amount' => 0,
        ];
    }
}
