<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Setting;

class CurrencyService
{
    /**
     * Get the active currency
     */
    public static function getActiveCurrency()
    {
        return Currency::active()->first();
    }

    /**
     * Convert amount to active currency
     * Assumes amount is in base currency (configurable)
     * exchange_rate represents how many base currency units = 1 unit of this currency
     */
    public static function convertToActive($amount, $fromCurrencyId = null)
    {
        $activeCurrency = self::getActiveCurrency();

        if (!$activeCurrency) {
            return $amount; // No conversion if no active currency
        }

        // If amount is in a specific currency, first convert to base
        if ($fromCurrencyId) {
            $fromCurrency = Currency::find($fromCurrencyId);
            if ($fromCurrency) {
                // Convert from currency to base: amount * from_exchange_rate
                $amount = $amount * $fromCurrency->exchange_rate;
            }
        }

        // Now convert from base to active currency: amount / active_exchange_rate
        return $amount / $activeCurrency->exchange_rate;
    }

    /**
     * Format amount with active currency symbol
     */
    public static function formatAmount($amount, $fromCurrencyId = null)
    {
        $convertedAmount = self::convertToActive($amount, $fromCurrencyId);
        $activeCurrency = self::getActiveCurrency();

        if ($activeCurrency) {
            return $activeCurrency->symbol . number_format($convertedAmount, 2);
        }

        return number_format($convertedAmount, 2);
    }
}
