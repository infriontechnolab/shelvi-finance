@php
    // Semantic tone → Tailwind classes. Lives in Blade so the scanner sees every
    // class (no @source inline safelist needed).
    $tones = [
        'success' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
        'warning' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
        'danger' => 'bg-red-500/15 text-red-600 dark:text-red-400',
        'info' => 'bg-sky-500/15 text-sky-600 dark:text-sky-400',
        'accent' => 'bg-violet-500/15 text-violet-600 dark:text-violet-400',
        'indigo' => 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400',
        'neutral' => 'bg-slate-500/15 text-slate-600 dark:text-slate-400',
    ];
    $cls = $tones[$tone] ?? 'bg-muted text-muted-foreground';
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $cls }}">
    <span class="size-1.5 rounded-full bg-current"></span>{{ $label }}
</span>
