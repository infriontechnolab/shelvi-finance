@props(['id', 'align' => 'end', 'width' => 'w-56'])

@php
    $alignClass = $align === 'end' ? 'right-0' : 'left-0';
@endphp

<div class="relative inline-block text-left">
    <div data-menu-trigger="{{ $id }}" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <div data-menu="{{ $id }}" hidden
        class="absolute {{ $alignClass }} z-50 mt-2 {{ $width }} origin-top-right overflow-hidden rounded-md border border-border bg-popover p-1 text-popover-foreground shadow-md">
        {{ $slot }}
    </div>
</div>
