@php
    // Semantic tone → Tailwind classes. Lives in Blade so the scanner sees every
    // class (no @source inline safelist needed).
    $tones = [
        'success' => 'bg-emerald-500/15 text-emerald-600',
        'warning' => 'bg-amber-500/15 text-amber-600',
        'danger' => 'bg-red-500/15 text-red-600',
        'info' => 'bg-sky-500/15 text-sky-600',
        'accent' => 'bg-violet-500/15 text-violet-600',
        'indigo' => 'bg-indigo-500/15 text-indigo-600',
        'neutral' => 'bg-slate-500/15 text-slate-600',
    ];
    $cls = $tones[$tone] ?? 'bg-muted text-muted-foreground';
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $cls }}">
    <span class="size-1.5 rounded-full bg-current"></span>{{ $label }}
</span>
