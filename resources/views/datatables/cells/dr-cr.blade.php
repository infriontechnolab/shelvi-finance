@php
    $cls = $type === 'CR'
        ? 'bg-amber-500/15 text-amber-600'
        : 'bg-sky-500/15 text-sky-600';
@endphp
<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $cls }}">{{ $type }}</span>
