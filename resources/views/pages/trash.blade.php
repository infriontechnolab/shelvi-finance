@php
    $total = collect($sections)->sum(fn ($s) => count($s['rows']));
@endphp

<x-layouts.admin title="Trash">
    <x-slot:subtitle>Soft-deleted records — restore them or delete permanently.</x-slot:subtitle>

    @if ($total === 0)
        <x-ui.card>
            <x-ui.card-content class="flex flex-col items-center gap-2 py-16 text-center">
                <span class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
                    <x-ui.icon name="trash-2" class="size-6" />
                </span>
                <p class="font-medium">Trash is empty</p>
                <p class="text-sm text-muted-foreground">Deleted parties, banks, cheques, transactions and ledger entries show up here.</p>
            </x-ui.card-content>
        </x-ui.card>
    @else
        <div class="space-y-6">
            @foreach ($sections as $section)
                @continue(count($section['rows']) === 0)
                <x-ui.card class="overflow-hidden">
                    <x-ui.card-header class="flex-row items-center gap-3 space-y-0">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-ui.icon :name="$section['icon']" class="size-5" />
                        </span>
                        <div>
                            <x-ui.card-title>{{ $section['label'] }}</x-ui.card-title>
                            <x-ui.card-description>{{ count($section['rows']) }} deleted</x-ui.card-description>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-0">
                        <ul class="divide-y divide-border border-t border-border">
                            @foreach ($section['rows'] as $row)
                                <li class="flex items-center gap-4 px-5 py-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium">{{ $row['primary'] }}</p>
                                        <p class="truncate text-sm text-muted-foreground">{{ $row['secondary'] }}</p>
                                    </div>
                                    <span class="hidden shrink-0 text-xs text-muted-foreground sm:block">Deleted {{ $row['when'] }}</span>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <form method="POST" action="{{ route('trash.restore', ['type' => $section['type'], 'id' => $row['id']]) }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="outline" size="sm">
                                                <x-ui.icon name="rotate-ccw" /> Restore
                                            </x-ui.button>
                                        </form>
                                        <form method="POST" action="{{ route('trash.destroy', ['type' => $section['type'], 'id' => $row['id']]) }}" data-delete-form>
                                            @csrf @method('DELETE')
                                            <x-ui.button type="submit" variant="ghost" size="sm"
                                                data-confirm="Permanently delete &ldquo;{{ $row['primary'] }}&rdquo;? This cannot be undone."
                                                class="text-destructive hover:text-destructive">
                                                <x-ui.icon name="trash-2" /> Delete forever
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card-content>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
