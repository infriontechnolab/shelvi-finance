@php
    $isEdit = $bank !== null;
    $title = $isEdit ? 'Edit Bank Account' : 'Add Bank Account';
    $val = fn (string $key, $default = '') => $bank[$key] ?? $default;
@endphp

<x-layouts.admin :title="$title">
    <x-slot:subtitle>{{ $isEdit ? 'Update the ' . $bank['name'] . ' account.' : 'Link a new bank account.' }}</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ route('banks') }}">
            <x-ui.icon name="arrow-left" /> Back to accounts
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card class="mx-auto max-w-3xl">
        <x-ui.card-header>
            <x-ui.card-title>Account Details</x-ui.card-title>
            <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            <form class="space-y-5" data-validate>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5 sm:col-span-2">
                        <x-ui.label for="b-name">Bank Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="b-name" name="name" value="{{ $val('name') }}" placeholder="e.g. HDFC Bank" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="b-account">Account Number <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="b-account" name="account" value="{{ $val('account') }}" placeholder="XXXX XXXX 0000" required
                            class="font-mono user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="b-type">Account Type <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="b-type" name="type" width="w-full" placeholder="Select type…"
                            :value="$val('type', 'Current')" :options="$types" />
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <x-ui.label for="b-holder">Account Holder <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="b-holder" name="holder" value="{{ $val('holder', 'Shelvi Financial Services') }}" placeholder="Account holder name" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="b-balance">{{ $isEdit ? 'Current Balance (₹)' : 'Opening Balance (₹)' }}</x-ui.label>
                        <x-ui.input id="b-balance" name="balance" type="number" min="0" value="{{ $val('balance', 0) }}" placeholder="0"
                            class="num font-mono tabular-nums" />
                    </div>
                </div>

                <div class="flex gap-2 border-t border-border pt-5">
                    <x-ui.button type="submit" size="sm">
                        <x-ui.icon name="check" /> {{ $isEdit ? 'Update account' : 'Add account' }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" href="{{ route('banks') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>
</x-layouts.admin>
