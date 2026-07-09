<x-layouts.admin title="Bank Accounts">
    <x-slot:subtitle>Balances and statements across all linked accounts.</x-slot:subtitle>
    <x-slot:actions>
        @can('trash.view')
            @if ($trashed)
                <x-ui.button variant="outline" size="sm" href="{{ route('banks') }}"><x-ui.icon name="arrow-left" /> Show active</x-ui.button>
            @else
                <x-ui.button variant="outline" size="sm" href="{{ route('banks', ['trashed' => 1]) }}"><x-ui.icon name="trash-2" /> Show deleted</x-ui.button>
            @endif
        @endcan
        @can('banks.create')
            @unless ($trashed)
                <x-ui.button size="sm" href="{{ route('banks.create') }}"><x-ui.icon name="plus" /> Add account</x-ui.button>
            @endunless
        @endcan
    </x-slot:actions>

    {{-- Account cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse ($banks as $b)
            <x-ui.card class="overflow-hidden {{ $trashed ? 'border-dashed opacity-90' : '' }}">
                <x-ui.card-content class="pt-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-sm font-bold text-primary">{{ $b['initials'] }}</span>
                            <div>
                                <p class="text-sm font-semibold leading-tight">{{ $b['name'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $b['type'] }} A/C</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @if ($trashed)
                                <span class="inline-flex items-center rounded-full bg-red-500/15 px-2 py-0.5 text-[11px] font-semibold text-red-600 dark:text-red-400">Deleted</span>
                                <form method="POST" action="{{ route('trash.restore', ['type' => 'banks', 'id' => $b['id']]) }}" class="inline">
                                    @csrf
                                    <button type="submit" title="Restore account"
                                        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                                        <x-ui.icon name="rotate-ccw" class="size-4" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('trash.destroy', ['type' => 'banks', 'id' => $b['id']]) }}" class="inline" data-delete-form>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete forever"
                                        data-confirm="Permanently delete {{ $b['name'] }}? This cannot be undone."
                                        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                                        <x-ui.icon name="trash-2" class="size-4" />
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Active</span>
                                @can('banks.update')
                                    <a href="{{ route('banks.edit', $b['id']) }}" title="Edit account"
                                        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                                        <x-ui.icon name="pencil" class="size-4" />
                                    </a>
                                @endcan
                                @can('banks.delete')
                                    <form method="POST" action="{{ route('banks.destroy', $b['id']) }}" class="inline" data-delete-form>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete account"
                                            data-confirm="Delete {{ $b['name'] }}? This action cannot be undone."
                                            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                                            <x-ui.icon name="trash-2" class="size-4" />
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-xs text-muted-foreground">Available Balance</p>
                        <p class="num font-mono text-2xl font-bold tabular-nums">{{ \App\Support\Inr::format($b['balance']) }}</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-border pt-3 text-xs">
                        <span class="font-mono text-muted-foreground">{{ $b['account'] }}</span>
                        <span class="text-muted-foreground">{{ $b['holder'] }}</span>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @empty
            <x-ui.card class="sm:col-span-2 xl:col-span-4">
                <x-ui.card-content class="py-12 text-center text-sm text-muted-foreground">
                    {{ $trashed ? 'No deleted bank accounts.' : 'No bank accounts yet.' }}
                </x-ui.card-content>
            </x-ui.card>
        @endforelse
    </div>

    {{-- Account statement (hidden while browsing deleted accounts) --}}
    @unless ($trashed)
    <div id="bank-txns-table-pagelen" hidden>
        <x-ui.combobox id="bankPageLen" value="10" width="w-32" searchPlaceholder="Rows…"
            :options="['10' => '10 rows', '25' => '25 rows', '50' => '50 rows', '100' => '100 rows']" />
    </div>

    <x-ui.card>
        <x-ui.card-header class="flex-row items-center justify-between space-y-0">
            <div class="space-y-1.5">
                <x-ui.card-title>Account Statement</x-ui.card-title>
                <x-ui.card-description>Recent credit and debit activity with running balance.</x-ui.card-description>
            </div>
            <x-ui.button variant="outline" size="sm" href="{{ route('banks.export') }}"><x-ui.icon name="file-spreadsheet" /> Export</x-ui.button>
        </x-ui.card-header>
        <x-ui.card-content>
            {{ $dataTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
    @endunless
</x-layouts.admin>
