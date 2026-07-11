<x-layouts.admin title="Cheque Management">
    <x-slot:subtitle>Track issued and received cheques through clearing.</x-slot:subtitle>
    <x-slot:actions>
        <x-trash-toggle table="cheques-table" />
        <x-ui.combobox id="chequeStatus" placeholder="All statuses" searchPlaceholder="Filter status…"
            :options="['' => 'All statuses', 'Pending' => 'Pending', 'Cleared' => 'Cleared', 'Bounced' => 'Bounced']" />
        <x-ui.button variant="outline" size="sm" href="{{ route('cheques.export') }}"><x-ui.icon name="file-spreadsheet" /> Export</x-ui.button>
        <x-ui.button variant="outline" size="sm" href="{{ route('cheques.export-pdf') }}"><x-ui.icon name="file-text" /> PDF</x-ui.button>
        @can('cheques.create')
            <x-ui.button size="sm" href="{{ route('cheques.create') }}"><x-ui.icon name="plus" /> New cheque</x-ui.button>
        @endcan
    </x-slot:actions>

    @php
        $tiles = [
            ['label' => 'Total Cheques', 'value' => $stats->total, 'tone' => ''],
            ['label' => 'Pending', 'value' => $stats->pending, 'tone' => 'text-amber-600'],
            ['label' => 'Cleared', 'value' => $stats->cleared, 'tone' => 'text-emerald-600'],
            ['label' => 'Bounced', 'value' => $stats->bounced, 'tone' => 'text-destructive'],
        ];
    @endphp

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($tiles as $t)
            <x-ui.card>
                <x-ui.card-content class="pt-6">
                    <p class="text-xs text-muted-foreground">{{ $t['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums {{ $t['tone'] }}">{{ $t['value'] }}</p>
                </x-ui.card-content>
            </x-ui.card>
        @endforeach
    </div>

    <div id="cheques-table-pagelen" hidden>
        <x-ui.combobox id="chequePageLen" value="10" width="w-32" searchPlaceholder="Rows…"
            :options="['10' => '10 rows', '25' => '25 rows', '50' => '50 rows']" />
    </div>

    <x-ui.card>
        <x-ui.card-content class="pt-6">
            {{ $dataTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
        <script type="module">
            document.getElementById('chequeStatus')?.addEventListener('change', () => {
                window.LaravelDataTables?.['cheques-table']?.draw();
            });
        </script>
    @endpush
</x-layouts.admin>
