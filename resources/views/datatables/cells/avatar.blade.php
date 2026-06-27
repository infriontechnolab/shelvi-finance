@php
    $parts = preg_split('/\s+/', trim($name));
    $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
@endphp
<div class="flex items-center gap-3">
    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">{{ $initials }}</span>
    <span class="font-medium text-foreground">{{ $name }}</span>
</div>
