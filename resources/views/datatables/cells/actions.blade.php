{{-- Row action buttons. Lucide icons render client-side; delete opens the confirm dialog. --}}
<div class="flex items-center justify-end gap-1">
    @if ($showEdit ?? true)
        <a href="{{ $editUrl ?? '#' }}" title="Edit" data-id="{{ $id }}"
            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
            <i data-lucide="pencil" class="size-4"></i>
        </a>
    @endif
    @if ($showDelete ?? true)
        <button type="button" title="Delete" data-id="{{ $id }}" data-confirm="{{ $deleteMessage }}"
            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
            <i data-lucide="trash-2" class="size-4"></i>
        </button>
    @endif
</div>
