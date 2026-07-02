{{-- Row action buttons. Lucide icons render client-side; delete submits a
     DELETE form (guarded by the confirm dialog when a deleteUrl is present). --}}
<div class="flex items-center justify-end gap-1">
    @if ($showEdit ?? true)
        <a href="{{ $editUrl ?? '#' }}" title="Edit" data-id="{{ $id }}"
            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
            <i data-lucide="pencil" class="size-4"></i>
        </a>
    @endif
    @if ($showDelete ?? true)
        @if (! empty($deleteUrl))
            <form method="POST" action="{{ $deleteUrl }}" class="inline" data-delete-form>
                @csrf
                @method('DELETE')
                <button type="submit" title="Delete" data-id="{{ $id }}" data-confirm="{{ $deleteMessage }}"
                    class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                    <i data-lucide="trash-2" class="size-4"></i>
                </button>
            </form>
        @else
            <button type="button" title="Delete" data-id="{{ $id }}" data-confirm="{{ $deleteMessage }}"
                class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                <i data-lucide="trash-2" class="size-4"></i>
            </button>
        @endif
    @endif
</div>
