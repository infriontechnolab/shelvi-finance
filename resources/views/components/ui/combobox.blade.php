@props([
    'id',                       // id of the hidden value input (read by DataTable filter hook)
    'name' => null,
    'options' => [],            // ['' => 'All statuses', 'Active' => 'Active', ...]
    'value' => '',
    'placeholder' => 'Select…',
    'searchPlaceholder' => 'Search…',
    'emptyText' => 'No results.',
    'width' => 'w-48',
    'required' => false,        // emits data-rule-required for jquery-validation
])

@php $selectedLabel = $options[$value] ?? $placeholder; @endphp

{{-- shadcn-style combobox, Blade-native (no React). JS in resources/js/app.js --}}
<div data-combobox class="relative {{ $width }}">
    <input type="hidden" id="{{ $id }}" @if ($name) name="{{ $name }}" @endif value="{{ $value }}" data-combobox-input
        @if ($required) data-rule-required="true" data-msg-required="This field is required." @endif>

    <button type="button" data-combobox-trigger
        class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 text-sm text-foreground shadow-sm transition-colors hover:bg-accent/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background">
        <span data-combobox-label class="truncate {{ $value === '' ? 'text-muted-foreground' : '' }}">{{ $selectedLabel }}</span>
        <x-ui.icon name="chevrons-up-down" class="size-4 shrink-0 opacity-50" />
    </button>

    <div data-combobox-popover hidden
        class="absolute left-0 z-50 mt-1 w-full min-w-[12rem] overflow-hidden rounded-md border border-border bg-popover text-popover-foreground shadow-md">
        <div class="flex items-center border-b border-border px-3">
            <x-ui.icon name="search" class="size-4 shrink-0 opacity-50" />
            <input type="text" data-combobox-search placeholder="{{ $searchPlaceholder }}"
                class="h-9 w-full bg-transparent px-2 text-sm outline-none placeholder:text-muted-foreground">
        </div>
        <div data-combobox-list class="max-h-60 overflow-y-auto p-1">
            @foreach ($options as $val => $label)
                <div data-combobox-item data-value="{{ $val }}" data-label="{{ $label }}"
                    aria-selected="{{ $val == $value ? 'true' : 'false' }}"
                    class="flex cursor-pointer items-center justify-between gap-2 rounded-sm px-2 py-1.5 text-sm transition-colors hover:bg-accent hover:text-accent-foreground">
                    <span>{{ $label }}</span>
                    <x-ui.icon name="check" data-combobox-check class="size-4 shrink-0 opacity-0" />
                </div>
            @endforeach
            <div data-combobox-empty hidden class="px-2 py-6 text-center text-sm text-muted-foreground">{{ $emptyText }}</div>
        </div>
    </div>
</div>
