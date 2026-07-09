@php
    $isReceived = $transaction->direction === 'received';
    $noun = $isReceived ? 'Receipt' : 'Payment';
    $listRoute = $isReceived ? route('money-received') : route('money-paid');
    $val = fn (string $key, $default = '') => old($key, $default);
@endphp

<x-layouts.admin title="Edit {{ $noun }}">
    <x-slot:subtitle>Update this {{ strtolower($noun) }} entry.</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ $listRoute }}">
            <x-ui.icon name="arrow-left" /> Back
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card class="mx-auto max-w-3xl">
        <x-ui.card-header>
            <x-ui.card-title>{{ $noun }} Details</x-ui.card-title>
            <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            <form class="space-y-5" data-validate method="POST" action="{{ route('transactions.update', $transaction->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="direction" value="{{ $transaction->direction }}">

                @if ($errors->any())
                    <div class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                        <p class="font-medium">Please fix the following:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <x-ui.label for="t-party">Party <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="t-party" name="party" width="w-full" placeholder="Select party…" searchPlaceholder="Search party…" :required="true"
                            :value="$val('party', $transaction->party?->name)" :options="['' => 'Select party…'] + $parties" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="t-customer">Customer Name</x-ui.label>
                        <x-ui.input id="t-customer" name="customer" placeholder="Customer name"
                            value="{{ $val('customer', $transaction->customer_name) }}" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="t-amount">Amount (₹) <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="t-amount" name="amount" type="number" min="0" required
                            value="{{ $val('amount', intdiv($transaction->amount, 100)) }}"
                            class="num font-mono tabular-nums user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="t-method">Method <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="t-method" name="method" width="w-full" placeholder="Select method…" searchPlaceholder="Search…" :required="true"
                            :value="$val('method', $transaction->method)" :options="['' => 'Select method…'] + $methods" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="t-bank">{{ $isReceived ? 'Deposit' : 'Source' }} Bank <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="t-bank" name="bank" width="w-full" placeholder="Select bank…" searchPlaceholder="Search bank…" :required="true"
                            :value="$val('bank', $transaction->bank?->account_number)" :options="['' => 'Select bank…'] + $banksList" />
                    </div>

                    @unless ($isReceived)
                        <div class="space-y-1.5">
                            <x-ui.label for="t-payee-holder">Account Holder Name</x-ui.label>
                            <x-ui.input id="t-payee-holder" name="payee_holder" placeholder="Payee's account holder name"
                                value="{{ $val('payee_holder', $transaction->payee_holder) }}" />
                        </div>

                        <div class="space-y-1.5">
                            <x-ui.label for="t-payee-account">Account Number</x-ui.label>
                            <x-ui.input id="t-payee-account" name="payee_account" placeholder="Payee's account number" class="font-mono"
                                value="{{ $val('payee_account', $transaction->payee_account_no) }}" />
                        </div>
                    @endunless

                    <div class="space-y-1.5">
                        <x-ui.label for="t-ref">Vehicle No</x-ui.label>
                        <x-ui.input id="t-ref" name="ref" placeholder="e.g. GJ01AB1234" class="font-mono"
                            value="{{ $val('ref', $transaction->reference) }}" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="t-date">Date <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.datepicker id="t-date" name="date" :required="true"
                            :value="$val('date', $transaction->txn_date?->format('Y-m-d'))" />
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <x-ui.label for="t-remark">Remark</x-ui.label>
                        <x-ui.textarea id="t-remark" name="remark" rows="2" placeholder="Purpose of this entry, e.g. token money">{{ $val('remark', $transaction->remark) }}</x-ui.textarea>
                    </div>
                </div>

                <div class="flex gap-2 border-t border-border pt-5">
                    <x-ui.button type="submit" size="sm"><x-ui.icon name="check" /> Update {{ strtolower($noun) }}</x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" href="{{ $listRoute }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>
</x-layouts.admin>
