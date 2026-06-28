<?php

namespace App\Support;

use InvalidArgumentException;
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

    public static function toCents(float|int|string|null $amount): int
    {
        $value = trim((string) ($amount ?? '0'));

        if ($value === '') {
            return 0;
        }

        if (str_contains(strtolower($value), 'e')) {
            $value = number_format((float) $value, 2, '.', '');
        }

        if (! preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            throw new InvalidArgumentException('Invalid money amount.');
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '-');

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        $cents = ((int) $whole * 100) + (int) $fraction;

        return $negative ? -$cents : $cents;
    }
}
