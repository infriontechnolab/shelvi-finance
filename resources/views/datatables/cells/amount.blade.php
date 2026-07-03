@php
    // $value is already INR-formatted. Tone drives the colour (Blade-scanned).
    $tones = [
        'positive' => 'text-emerald-600 dark:text-emerald-400',
        'negative' => 'text-destructive',
        'muted' => 'text-muted-foreground',
        'plain' => '',
    ];
@endphp
<span class="num font-mono tabular-nums {{ ($bold ?? true) ? 'font-semibold' : '' }} {{ $tones[$tone ?? 'plain'] ?? '' }}">{{ $value }}</span>
