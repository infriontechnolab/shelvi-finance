<x-layouts.admin title="Money Received">
    <x-slot:subtitle>Record and track inbound collections from parties.</x-slot:subtitle>
    @can('trash.view')
        <x-slot:actions>
            <x-trash-toggle table="received-table" />
        </x-slot:actions>
    @endcan

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Entry form --}}
        <x-ui.card class="lg:col-span-1">
            <x-ui.card-header>
                <x-ui.card-title>New Receipt</x-ui.card-title>
                <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
            </x-ui.card-header>
            <x-ui.card-content>
                <form class="space-y-4" data-validate method="POST" action="{{ route('money-received.store') }}">
                    @csrf
                    <input type="hidden" name="direction" value="received">
                    @if ($errors->any())
                        <div class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-xs text-destructive">
                            <ul class="list-inside list-disc">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="space-y-1.5">
                        <x-ui.label for="r-party">Party <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="r-party" name="party" width="w-full" placeholder="Select party…" searchPlaceholder="Search party…" :required="true"
                            :value="old('party')" :options="['' => 'Select party…'] + $parties" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="r-amount">Amount (₹) <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="r-amount" name="amount" type="number" min="0" placeholder="0" required value="{{ old('amount') }}"
                            class="num font-mono tabular-nums user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="r-method">Method <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="r-method" name="method" width="w-full" placeholder="Select method…" searchPlaceholder="Search…" :required="true"
                            :value="old('method')" :options="['' => 'Select method…'] + $methods" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="r-bank">Deposit Bank <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="r-bank" name="bank" width="w-full" placeholder="Select bank…" searchPlaceholder="Search bank…" :required="true"
                            :value="old('bank')" :options="['' => 'Select bank…'] + $banksList" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="r-ref">Reference No</x-ui.label>
                        <x-ui.input id="r-ref" name="ref" placeholder="NEFT / UPI / Cheque ref" class="font-mono" value="{{ old('ref') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="r-date">Date <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.datepicker id="r-date" name="date" :value="old('date')" :required="true" />
                    </div>
                    <div class="flex gap-2 pt-2">
                        <x-ui.button type="submit" size="sm" class="flex-1"><x-ui.icon name="check" /> Save receipt</x-ui.button>
                        <x-ui.button type="reset" variant="outline" size="sm">Clear</x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        {{-- History --}}
        <div class="space-y-4 lg:col-span-2">
            <div id="received-table-pagelen" hidden>
                <x-ui.combobox id="receivedPageLen" value="10" width="w-32" searchPlaceholder="Rows…"
                    :options="['10' => '10 rows', '25' => '25 rows', '50' => '50 rows']" />
            </div>
            <x-ui.card>
                <x-ui.card-header>
                    <x-ui.card-title>Recent Receipts</x-ui.card-title>
                    <x-ui.card-description>Collections recorded across all banks.</x-ui.card-description>
                </x-ui.card-header>
                <x-ui.card-content>
                    {{ $dataTable->table(['class' => 'w-full text-sm']) }}
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-layouts.admin>
