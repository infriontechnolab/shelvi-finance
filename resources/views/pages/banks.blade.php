<x-layouts.admin title="Bank Accounts">
    <x-slot:subtitle>Balances and statements across all linked accounts.</x-slot:subtitle>
    <x-slot:actions>
        @can('banks.create')
            <x-ui.button size="sm" href="{{ route('banks.create') }}"><x-ui.icon name="plus" /> Add account</x-ui.button>
        @endcan
    </x-slot:actions>

    {{-- Account cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($banks as $b)
            <x-ui.card class="overflow-hidden">
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
                            <span class="inline-flex items-center rounded-full bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-600">Active</span>
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
        @endforeach
    </div>

    {{-- Account statement --}}
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
            <x-ui.button variant="outline" size="sm"><x-ui.icon name="plus" /> Export</x-ui.button>
        </x-ui.card-header>
        <x-ui.card-content>
            {{ $dataTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-layouts.admin>
