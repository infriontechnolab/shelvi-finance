<x-layouts.admin title="Money Paid">
    <x-slot:subtitle>Record and track outbound payments to parties.</x-slot:subtitle>
    @can('trash.view')
        <x-slot:actions>
            <x-trash-toggle table="paid-table" />
        </x-slot:actions>
    @endcan

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Entry form --}}
        <x-ui.card class="lg:col-span-1">
            <x-ui.card-header>
                <x-ui.card-title>New Payment</x-ui.card-title>
                <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
            </x-ui.card-header>
            <x-ui.card-content>
                <form class="space-y-4" data-validate method="POST" action="{{ route('money-paid.store') }}">
                    @csrf
                    <input type="hidden" name="direction" value="paid">
                    @if ($errors->any())
                        <div class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-xs text-destructive">
                            <ul class="list-inside list-disc">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="space-y-1.5">
                        <x-ui.label for="p-party">Party <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-party" name="party" width="w-full" placeholder="Select party…" searchPlaceholder="Search party…" :required="true"
                            :value="old('party')" :options="['' => 'Select party…'] + $parties" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-customer">Customer Name</x-ui.label>
                        <x-ui.input id="p-customer" name="customer" placeholder="Customer name" value="{{ old('customer') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-amount">Amount (₹) <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="p-amount" name="amount" type="number" min="0" placeholder="0" required value="{{ old('amount') }}"
                            class="num font-mono tabular-nums user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-method">Method <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-method" name="method" width="w-full" placeholder="Select method…" searchPlaceholder="Search…" :required="true"
                            :value="old('method')" :options="['' => 'Select method…'] + $methods" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-bank">Source Bank <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-bank" name="bank" width="w-full" placeholder="Select bank…" searchPlaceholder="Search bank…" :required="true"
                            :value="old('bank')" :options="['' => 'Select bank…'] + $banksList" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-payee-holder">Account Holder Name</x-ui.label>
                        <x-ui.input id="p-payee-holder" name="payee_holder" placeholder="Payee's account holder name" value="{{ old('payee_holder') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-payee-account">Account Number</x-ui.label>
                        <x-ui.input id="p-payee-account" name="payee_account" placeholder="Payee's account number" class="font-mono" value="{{ old('payee_account') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-ref">Vehicle No</x-ui.label>
                        <x-ui.input id="p-ref" name="ref" placeholder="e.g. GJ01AB1234" class="font-mono" value="{{ old('ref') }}" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-date">Date <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.datepicker id="p-date" name="date" :value="old('date')" :required="true" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-remark">Remark</x-ui.label>
                        <x-ui.textarea id="p-remark" name="remark" rows="2" placeholder="Purpose of this payment, e.g. token money">{{ old('remark') }}</x-ui.textarea>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <x-ui.button type="submit" size="sm" class="flex-1"><x-ui.icon name="check" /> Save payment</x-ui.button>
                        <x-ui.button type="reset" variant="outline" size="sm">Clear</x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        {{-- History --}}
        <div class="space-y-4 lg:col-span-2">
            <div id="paid-table-pagelen" hidden>
                <x-ui.combobox id="paidPageLen" value="10" width="w-32" searchPlaceholder="Rows…"
                    :options="['10' => '10 rows', '25' => '25 rows', '50' => '50 rows']" />
            </div>
            <x-ui.card>
                <x-ui.card-header class="flex-row items-center justify-between space-y-0">
                    <div class="space-y-1.5">
                        <x-ui.card-title>Recent Payments</x-ui.card-title>
                        <x-ui.card-description>Payments recorded across all banks.</x-ui.card-description>
                    </div>
                    <x-ui.button variant="outline" size="sm" href="{{ route('money-paid.export') }}"><x-ui.icon name="file-spreadsheet" /> Export</x-ui.button>
                    <x-ui.button variant="outline" size="sm" href="{{ route('money-paid.export-pdf') }}"><x-ui.icon name="file-text" /> PDF</x-ui.button>
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
