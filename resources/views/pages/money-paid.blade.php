<x-layouts.admin title="Money Paid">
    <x-slot:subtitle>Record and track outbound payments to parties.</x-slot:subtitle>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Entry form --}}
        <x-ui.card class="lg:col-span-1">
            <x-ui.card-header>
                <x-ui.card-title>New Payment</x-ui.card-title>
                <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
            </x-ui.card-header>
            <x-ui.card-content>
                <form class="space-y-4" data-validate>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-party">Party <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-party" name="party" width="w-full" placeholder="Select party…" searchPlaceholder="Search party…" :required="true"
                            :options="['' => 'Select party…'] + $parties" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-amount">Amount (₹) <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="p-amount" name="amount" type="number" min="0" placeholder="0" required
                            class="num font-mono tabular-nums user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-method">Method <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-method" name="method" width="w-full" placeholder="Select method…" searchPlaceholder="Search…" :required="true"
                            :options="['' => 'Select method…', 'Online' => 'Online', 'UPI' => 'UPI', 'Cheque' => 'Cheque', 'Cash' => 'Cash']" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-bank">Source Bank <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-bank" name="bank" width="w-full" placeholder="Select bank…" searchPlaceholder="Search bank…" :required="true"
                            :options="['' => 'Select bank…'] + $banksList" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-ref">Reference No</x-ui.label>
                        <x-ui.input id="p-ref" name="ref" placeholder="NEFT / UPI / Cheque ref" class="font-mono" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label for="p-date">Date <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="p-date" name="date" type="date" required class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
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
                <x-ui.card-header>
                    <x-ui.card-title>Recent Payments</x-ui.card-title>
                    <x-ui.card-description>Payments recorded across all banks.</x-ui.card-description>
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
