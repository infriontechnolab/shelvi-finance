{{-- Recycle-bin row actions: restore (POST) + delete-forever (DELETE, confirmed). --}}
<div class="flex items-center justify-end gap-1">
    <form method="POST" action="{{ $restoreUrl }}" class="inline">
        @csrf
        <button type="submit" title="Restore"
            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-border bg-background px-2.5 text-xs font-medium text-foreground transition-colors hover:bg-accent">
            <i data-lucide="rotate-ccw" class="size-3.5"></i> Restore
        </button>
    </form>
    <form method="POST" action="{{ $deleteUrl }}" class="inline" data-delete-form>
        @csrf
        @method('DELETE')
        <button type="submit" title="Delete forever"
            data-confirm="Permanently delete &ldquo;{{ $label }}&rdquo;? This cannot be undone."
            class="inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
            <i data-lucide="trash-2" class="size-3.5"></i> Delete
        </button>
    </form>
</div>
