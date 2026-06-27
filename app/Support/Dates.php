<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Display formatting for the ISO (Y-m-d) dates stored in demo data.
 * Data is kept ISO so DataTables sort chronologically; views render human form.
 */
class Dates
{
    /** ISO date → "DD MMM YYYY". Null / "-" → em dash. */
    public static function human(?string $iso): string
    {
        if ($iso === null || $iso === '' || $iso === '-') {
            return '—';
        }

        return Carbon::parse($iso)->format('d M Y');
    }
}
