@php
    $variants = [
        'mono' => 'font-mono text-xs text-muted-foreground',
        'bold' => 'font-semibold',
        'muted' => 'text-muted-foreground',
        'plain' => '',
    ];
@endphp
<span class="{{ $variants[$variant ?? 'plain'] ?? '' }}">{{ $value }}</span>
