{{-- Transient toast (text + visibility toggled from app.js). --}}
<div data-toast hidden
    class="fixed bottom-4 right-4 z-[110] flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-3 text-sm font-medium text-card-foreground shadow-lg">
    <x-ui.icon name="check" class="size-4 text-emerald-500" />
    <span data-toast-message></span>
</div>
