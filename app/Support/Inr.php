<?php

namespace App\Support;

/**
 * Indian Rupee formatting (lakh/crore grouping): ₹XX,XX,XXX.
 * Design-only helper used by pages + DataTable renderers.
 */
class Inr
{
    /** Format an amount with Indian digit grouping. */
    public static function format(int|float $n, bool $symbol = true): string
    {
        $sign = $n < 0 ? '-' : '';
        $abs = (string) abs((int) round($n));

        $last3 = substr($abs, -3);
        $rest = substr($abs, 0, -3);

        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $last3 = $rest.','.$last3;
        }

        return $sign.($symbol ? '₹' : '').$last3;
    }
}
