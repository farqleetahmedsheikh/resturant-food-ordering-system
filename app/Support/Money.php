<?php

namespace App\Support;

use NumberFormatter;

class Money
{
    public static function code(): string
    {
        return (string) config('restaurant.currency.code', 'AUD');
    }

    public static function locale(): string
    {
        return (string) config('restaurant.currency.locale', 'en_AU');
    }

    public static function format(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter(self::locale(), NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($value, self::code());

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return self::code().' $'.number_format($value, 2);
    }
}
