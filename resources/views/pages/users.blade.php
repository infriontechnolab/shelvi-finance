<x-layouts.admin title="User Management">
    <x-slot:subtitle>Manage who can sign in and what they can do.</x-slot:subtitle>
    <x-slot:actions>
        @can('users.create')
            <x-ui.button size="sm" href="{{ route('users.create') }}"><x-ui.icon name="plus" /> New user</x-ui.button>
        @endcan
    </x-slot:actions>

    <div id="users-table-pagelen" hidden>
        <x-ui.combobox id="userPageLen" value="10" width="w-32" searchPlaceholder="Rows…"
            :options="['10' => '10 rows', '25' => '25 rows', '50' => '50 rows', '100' => '100 rows']" />
    </div>

    <x-ui.card>
        <x-ui.card-content class="pt-6">
            {{ $dataTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-layouts.admin>
