<x-layouts.admin title="Party Ledger">
    <x-slot:subtitle>Statement of account with running balance.</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.combobox id="ledgerParty" :value="$ledgerParty" placeholder="Select party…" searchPlaceholder="Search party…"
            :options="$parties" />
        <x-ui.button variant="outline" size="sm"><x-ui.icon name="plus" /> Export</x-ui.button>
    </x-slot:actions>

    @php
        $cards = [
            ['label' => 'Opening Balance', 'value' => $summary->opening, 'tone' => ''],
            ['label' => 'Total Debit', 'value' => $summary->totalDebit, 'tone' => 'text-destructive'],
            ['label' => 'Total Credit', 'value' => $summary->totalCredit, 'tone' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Closing Balance', 'value' => $summary->closing, 'tone' => '', 'badge' => $summary->closingType],
        ];
    @endphp

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $c)
            <x-ui.card>
                <x-ui.card-content class="pt-6">
                    <p class="text-xs text-muted-foreground">{{ $c['label'] }}</p>
                    <p class="num mt-1 font-mono text-xl font-bold tabular-nums {{ $c['tone'] }}">
                        {{ \App\Support\Inr::format($c['value']) }}
                        @isset($c['badge'])
                            <span class="inline-flex items-center rounded-full bg-sky-500/15 px-2 py-0.5 align-middle text-[11px] font-semibold text-sky-600 dark:text-sky-400">{{ $c['badge'] }}</span>
                        @endisset
                    </p>
                </x-ui.card-content>
            </x-ui.card>
        @endforeach
    </div>

    {{-- Ledger --}}
    <div id="ledger-table-pagelen" hidden>
        <x-ui.combobox id="ledgerPageLen" value="25" width="w-32" searchPlaceholder="Rows…"
            :options="['25' => '25 rows', '50' => '50 rows', '100' => '100 rows']" />
    </div>

    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title>{{ $ledgerParty }} — Ledger</x-ui.card-title>
            <x-ui.card-description>{{ \App\Support\Dates::human($summary->from) }} to {{ \App\Support\Dates::human($summary->to) }} · all vouchers.</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            {{ $dataTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-layouts.admin>
