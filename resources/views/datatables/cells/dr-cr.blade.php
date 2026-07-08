@php
    // 'asset' scheme (default, e.g. party overview): DR = receivable (green) / CR = payable (red).
    // 'entry' scheme (e.g. ledger statement): matches the debit/credit column colours — DR = red, CR = green.
    $green = ($scheme ?? 'asset') === 'asset' ? $type === 'DR' : $type === 'CR';
    $cls = $green
        ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
        : 'bg-destructive/15 text-destructive';
@endphp
<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $cls }}">{{ $type }}</span>
