@props(['initials' => '', 'size' => 'size-9'])

<span {{ $attributes->merge(['class' => "relative flex $size shrink-0 overflow-hidden rounded-full bg-muted items-center justify-center text-xs font-medium text-muted-foreground"]) }}>
    {{ $initials }}
</span>
