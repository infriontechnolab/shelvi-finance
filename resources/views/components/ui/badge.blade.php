@props(['variant' => 'default'])

@php
    $base = 'inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none';
    $variants = [
        'default' => 'border-transparent bg-primary text-primary-foreground shadow',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground',
        'destructive' => 'border-transparent bg-destructive text-destructive-foreground shadow',
        'outline' => 'text-foreground border-border',
        'success' => 'border-transparent bg-emerald-500/15 text-emerald-600',
        'warning' => 'border-transparent bg-amber-500/15 text-amber-600',
    ];
@endphp

<span {{ $attributes->merge(['class' => trim("$base {$variants[$variant]}")]) }}>
    {{ $slot }}
</span>
