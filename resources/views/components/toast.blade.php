@php
    // Global toast system. Any controller flash (success/error/warning/info) is
    // surfaced on load; app.js also exposes window.showToast(message, type) for
    // client-side use. One <template> per variant keeps every Tailwind class in a
    // scanned Blade file (JS only clones — it never composes class strings).
    $variants = [
        'success' => ['icon' => 'circle-check', 'box' => 'bg-emerald-600 text-white'],
        'error' => ['icon' => 'alert-circle', 'box' => 'bg-red-600 text-white'],
        'warning' => ['icon' => 'triangle-alert', 'box' => 'bg-amber-500 text-white'],
        'info' => ['icon' => 'info', 'box' => 'bg-sky-600 text-white'],
    ];
    $flashes = collect(array_keys($variants))
        ->mapWithKeys(fn ($k) => [$k => session($k)])
        ->filter()
        ->all();
@endphp

{{-- Stack: top-right, newest below the previous. Colour signals type. --}}
<div data-toast-region class="pointer-events-none fixed right-4 top-4 z-[110] flex w-full max-w-sm flex-col items-end gap-2"></div>

@foreach ($variants as $type => $v)
    <template data-toast-tpl="{{ $type }}">
        <div class="pointer-events-auto flex w-full items-start gap-2.5 rounded-lg px-4 py-3 text-sm font-medium shadow-lg {{ $v['box'] }}">
            <x-ui.icon name="{{ $v['icon'] }}" class="mt-0.5 size-4 shrink-0" />
            <span data-toast-message class="flex-1 leading-snug"></span>
            <button type="button" data-toast-close aria-label="Dismiss" class="-mr-1 text-white/70 transition-colors hover:text-white">
                <x-ui.icon name="x" class="size-4" />
            </button>
        </div>
    </template>
@endforeach

@if ($flashes)
    <script type="application/json" data-flash-json>@json($flashes)</script>
@endif
