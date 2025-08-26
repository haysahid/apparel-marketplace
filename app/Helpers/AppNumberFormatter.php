<?php

namespace App\Helpers;

use NumberFormatter;

class AppNumberFormatter
{
    /**
     * Format a number to Indonesian Rupiah currency format.
     *
     * @param float|int $value
     * @return string
     */
    public static function formatCurrency($value, $currency = 'IDR')
    {
        if ($value === null || $value === '') {
            $value = 0;
        }

        $formatter = NumberFormatter::create('id_ID', NumberFormatter::CURRENCY);

        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        return $formatter->formatCurrency($value, $currency);
    }

    public static function formatNumber($value)
    {
        if ($value === null || $value === '') {
            $value = 0;
        }

        $formatter = NumberFormatter::create('id_ID', NumberFormatter::DECIMAL);

        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        return $formatter->format($value);
    }
}
