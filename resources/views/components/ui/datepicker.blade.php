@props([
    'name',
    'value' => '',
    'id' => null,
    'required' => false,
    'placeholder' => 'Select date…',
])

@php
    $id = $id ?? $name;
    $value = $value ?: '';
    $label = $value ? \App\Support\Dates::human($value) : $placeholder;
@endphp

{{-- Styled datepicker: hidden Y-m-d input + calendar popover. JS in app.js. --}}
<div data-datepicker class="relative">
    <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
        data-datepicker-value @if ($required) data-rule-required @endif>

    <button type="button" data-datepicker-trigger
        class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 text-sm shadow-sm transition-colors hover:bg-accent/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
        <span data-datepicker-label class="{{ $value ? '' : 'text-muted-foreground' }}">{{ $label }}</span>
        <i data-lucide="calendar" class="size-4 shrink-0 opacity-50"></i>
    </button>

    <div data-datepicker-popover hidden
        class="absolute left-0 z-50 mt-1 w-[17rem] rounded-md border border-border bg-popover p-3 text-popover-foreground shadow-md">
        <div class="mb-2 flex items-center justify-between">
            <button type="button" data-datepicker-prev aria-label="Previous month"
                class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                <i data-lucide="chevron-left" class="size-4"></i>
            </button>
            <span data-datepicker-title class="text-sm font-medium"></span>
            <button type="button" data-datepicker-next aria-label="Next month"
                class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                <i data-lucide="chevron-right" class="size-4"></i>
            </button>
        </div>
        <div class="mb-1 grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-muted-foreground">
            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
        </div>
        <div data-datepicker-grid class="grid grid-cols-7 gap-1"></div>
    </div>
</div>
