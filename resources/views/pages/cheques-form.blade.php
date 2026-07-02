@php
    $isEdit = $cheque !== null;
    $title = $isEdit ? 'Edit Cheque' : 'New Cheque';
    // Prefer old() so server-side validation errors repopulate the form.
    $val = fn (string $key, $default = '') => old($key, $cheque[$key] ?? $default);
@endphp

<x-layouts.admin :title="$title">
    <x-slot:subtitle>{{ $isEdit ? 'Update cheque #' . $cheque['no'] . '.' : 'Register an issued or received cheque.' }}</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ route('cheques') }}">
            <x-ui.icon name="arrow-left" /> Back to list
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card class="mx-auto max-w-3xl">
        <x-ui.card-header>
            <x-ui.card-title>Cheque Details</x-ui.card-title>
            <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            <form class="space-y-5" data-validate method="POST"
                action="{{ $isEdit ? route('cheques.update', $cheque['id']) : route('cheques.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

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
                        <x-ui.label for="c-no">Cheque No <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="c-no" name="no" value="{{ $val('no') }}" placeholder="e.g. 004522" required
                            class="font-mono user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-amount">Amount (₹) <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="c-amount" name="amount" type="number" min="0" value="{{ $val('amount') }}" placeholder="0" required
                            class="num font-mono tabular-nums user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-party">Party <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="c-party" name="party" width="w-full" placeholder="Select party…" :required="true"
                            :value="$val('party')" :options="['' => 'Select party…'] + $parties" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-bank">Bank <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="c-bank" name="bank" width="w-full" placeholder="Select bank…" :required="true"
                            :value="$val('bank')" :options="['' => 'Select bank…'] + $banksList" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-issue">Issue Date <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="c-issue" name="issue" type="date" value="{{ $val('issue') }}" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-deposit">Deposit Date</x-ui.label>
                        <x-ui.input id="c-deposit" name="deposit" type="date" value="{{ $val('deposit') }}" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-due">Due Date <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="c-due" name="due" type="date" value="{{ $val('due') }}" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="c-status">Status</x-ui.label>
                        <x-ui.combobox id="c-status" name="status" width="w-full" placeholder="Select status…"
                            :value="$val('status', 'Pending')" :options="$statuses" />
                    </div>
                </div>

                <div class="flex gap-2 border-t border-border pt-5">
                    <x-ui.button type="submit" size="sm">
                        <x-ui.icon name="check" /> {{ $isEdit ? 'Update cheque' : 'Create cheque' }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" href="{{ route('cheques') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>
</x-layouts.admin>
