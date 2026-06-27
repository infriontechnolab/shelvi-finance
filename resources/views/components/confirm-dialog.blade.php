{{-- Reusable confirm dialog (opened by any [data-confirm] control; logic in app.js). --}}
<div data-confirm-modal hidden class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div data-confirm-backdrop class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div role="alertdialog" aria-modal="true" aria-labelledby="confirm-title"
        class="relative w-full max-w-sm rounded-xl border border-border bg-card p-6 text-card-foreground shadow-lg">
        <div class="flex items-start gap-4">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                <x-ui.icon name="alert-circle" class="size-5" />
            </span>
            <div class="space-y-1">
                <h2 id="confirm-title" class="text-base font-semibold">Are you sure?</h2>
                <p data-confirm-message class="text-sm text-muted-foreground">This action cannot be undone.</p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button data-confirm-cancel variant="outline" size="sm">Cancel</x-ui.button>
            <button type="button" data-confirm-ok
                class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-destructive px-4 text-sm font-medium text-destructive-foreground shadow-sm transition-colors hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-destructive/40 focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                <x-ui.icon name="trash-2" class="size-4" /> Delete
            </button>
        </div>
    </div>
</div>
