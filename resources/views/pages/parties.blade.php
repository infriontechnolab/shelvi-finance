<x-layouts.admin title="Party Management">
    <x-slot:subtitle>Customers, vendors, finance companies and agencies.</x-slot:subtitle>
    <x-slot:actions>
        <x-trash-toggle table="parties-table" />
        <x-ui.combobox id="partyCategory" placeholder="All categories" searchPlaceholder="Filter category…"
            :options="['' => 'All categories', 'Customer' => 'Customer', 'Vendor' => 'Vendor', 'Finance Co' => 'Finance Co', 'Agency' => 'Agency']" />
        <x-ui.button variant="outline" size="sm" href="{{ route('parties.export') }}"><x-ui.icon name="file-spreadsheet" /> Export</x-ui.button>
        @can('parties.create')
            <x-ui.button size="sm" href="{{ route('parties.create') }}"><x-ui.icon name="plus" /> New party</x-ui.button>
        @endcan
    </x-slot:actions>

    <div id="parties-table-pagelen" hidden>
        <x-ui.combobox id="partyPageLen" value="10" width="w-32" searchPlaceholder="Rows…"
            :options="['10' => '10 rows', '25' => '25 rows', '50' => '50 rows', '100' => '100 rows']" />
    </div>

    <x-ui.card>
        <x-ui.card-content class="pt-6">
            {{ $dataTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
        <script type="module">
            document.getElementById('partyCategory')?.addEventListener('change', () => {
                window.LaravelDataTables?.['parties-table']?.draw();
            });
        </script>
    @endpush
</x-layouts.admin>
