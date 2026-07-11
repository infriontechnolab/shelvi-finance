<?php

namespace App\Support;

/**
 * Colour helpers for PDF exports (DomPDF), mirroring the same semantic tones
 * used by the on-screen DataTable cell partials (resources/views/datatables/cells/*)
 * so a downloaded PDF looks like the page it came from, not a plain table.
 */
class PdfCell
{
    /** bg-*-500/15 + text-*-600, matching status-pill.blade.php's $tones map. */
    private const TONES = [
        'success' => ['bg' => 'rgba(16,185,129,.15)', 'fg' => '#059669'],
        'warning' => ['bg' => 'rgba(245,158,11,.15)', 'fg' => '#d97706'],
        'danger' => ['bg' => 'rgba(239,68,68,.15)', 'fg' => '#dc2626'],
        'info' => ['bg' => 'rgba(14,165,233,.15)', 'fg' => '#0284c7'],
        'accent' => ['bg' => 'rgba(139,92,246,.15)', 'fg' => '#7c3aed'],
        'indigo' => ['bg' => 'rgba(99,102,241,.15)', 'fg' => '#4f46e5'],
        'neutral' => ['bg' => 'rgba(100,116,139,.15)', 'fg' => '#475569'],
    ];

    private const GREEN = '#059669';

    private const RED = '#dc2626';

    private const MUTED = '#71717a';

    private const FONT = "font-family:'DejaVu Sans',sans-serif;";

    /** Plain amount, no colour. */
    public static function plain(string $value): string
    {
        return e($value);
    }

    /** Signed amount: tone is 'positive' (green), 'negative' (red), or 'muted' (grey). */
    public static function amount(string $value, string $tone): string
    {
        $color = match ($tone) {
            'positive' => self::GREEN,
            'negative' => self::RED,
            default => self::MUTED,
        };

        // NOTE: font-weight must be 'bold'/700, not 600 — dompdf silently
        // substitutes a font missing the ₹ glyph for non-standard weights.
        return '<span style="'.self::FONT.'color:'.$color.';font-weight:bold">'.e($value).'</span>';
    }

    /** Rounded pill badge — same tone keys as status-pill.blade.php ($tones). */
    public static function pill(string $label, string $tone): string
    {
        $c = self::TONES[$tone] ?? ['bg' => 'rgba(113,113,122,.15)', 'fg' => self::MUTED];

        return '<span style="'.self::FONT.'display:inline-block;padding:2px 8px;border-radius:9999px;'
            .'font-size:9px;font-weight:700;background:'.$c['bg'].';color:'.$c['fg'].'">'.e($label).'</span>';
    }

    /** DR/CR pill. 'asset' scheme: DR=green/CR=red. 'entry' scheme: DR=red/CR=green. */
    public static function drCr(string $type, string $scheme = 'asset'): string
    {
        $green = $scheme === 'asset' ? $type === 'DR' : $type === 'CR';

        return self::pill($type, $green ? 'success' : 'danger');
    }

    public static function muted(string $value): string
    {
        return '<span style="'.self::FONT.'color:'.self::MUTED.'">'.e($value).'</span>';
    }
}
