@props([
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'label' => null,
])

{{-- shadcn-style toggle: a hidden checkbox drives a rounded track + knob via peer-*. --}}
<label class="inline-flex select-none items-center gap-2 {{ $disabled ? 'opacity-60' : 'cursor-pointer' }}">
    <span class="relative inline-flex shrink-0">
        <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($checked) @disabled($disabled)
            {{ $attributes->merge(['class' => 'peer sr-only']) }} />
        <span class="h-5 w-9 rounded-full bg-muted transition-colors peer-checked:bg-primary
            peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background"></span>
        <span class="pointer-events-none absolute left-0.5 top-0.5 size-4 rounded-full bg-background shadow-sm transition-transform peer-checked:translate-x-4"></span>
    </span>
    @if ($label)
        <span class="text-sm text-foreground">{{ $label }}</span>
    @endif
</label>
