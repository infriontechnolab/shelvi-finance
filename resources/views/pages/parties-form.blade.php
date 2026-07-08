@php
    // One view, two modes: $party === null → create, else edit (prefilled).
    $isEdit = $party !== null;
    $title = $isEdit ? 'Edit Party' : 'New Party';
    // Prefer old() so server-side validation errors repopulate the form.
    $val = fn (string $key, $default = '') => old($key, $party[$key] ?? $default);
@endphp

<x-layouts.admin :title="$title">
    <x-slot:subtitle>{{ $isEdit ? 'Update the details for ' . $party['name'] . '.' : 'Add a customer, vendor, finance company or agency.' }}</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ route('parties') }}">
            <x-ui.icon name="arrow-left" /> Back to list
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card class="mx-auto max-w-3xl">
        <x-ui.card-header>
            <x-ui.card-title>Party Details</x-ui.card-title>
            <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            <form class="space-y-5" data-validate method="POST"
                action="{{ $isEdit ? route('parties.update', $party['id']) : route('parties.store') }}">
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
                    <div class="space-y-1.5 sm:col-span-2">
                        <x-ui.label for="p-name">Party Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="p-name" name="name" value="{{ $val('name') }}" placeholder="e.g. Mehta Traders" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="p-category">Category <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="p-category" name="category" width="w-full" placeholder="Select category…" :required="true"
                            :value="$val('category')" :options="['' => 'Select category…'] + $categories" />
                    </div>

                    @if ($isEdit)
                        <div class="space-y-1.5">
                            <x-ui.label for="p-phone">Phone <span class="text-destructive">*</span></x-ui.label>
                            <x-ui.input id="p-phone" name="phone" type="tel" value="{{ $val('phone') }}" placeholder="10-digit mobile" required
                                class="font-mono user-invalid:border-destructive user-invalid:ring-destructive/20" />
                        </div>

                        <div class="space-y-1.5">
                            <x-ui.label for="p-opening">Opening Balance (₹)</x-ui.label>
                            <x-ui.input id="p-opening" name="opening" type="number" min="0" value="{{ $val('opening', 0) }}" placeholder="0"
                                class="num font-mono tabular-nums" />
                        </div>

                        <div class="space-y-1.5">
                            <x-ui.label for="p-baltype">Balance Type</x-ui.label>
                            <x-ui.combobox id="p-baltype" name="balType" width="w-full" placeholder="DR / CR"
                                :value="$val('balType', 'DR')" :options="$balTypes" />
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <x-ui.label for="p-limit">Credit Limit (₹)</x-ui.label>
                        <x-ui.input id="p-limit" name="limit" type="number" min="0" value="{{ $val('limit', 0) }}" placeholder="0"
                            class="num font-mono tabular-nums" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="p-status">Status</x-ui.label>
                        <x-ui.combobox id="p-status" name="status" width="w-full" placeholder="Select status…"
                            :value="$val('status', 'Active')" :options="$statuses" />
                    </div>
                </div>

                <div class="flex gap-2 border-t border-border pt-5">
                    <x-ui.button type="submit" size="sm">
                        <x-ui.icon name="check" /> {{ $isEdit ? 'Update party' : 'Create party' }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" href="{{ route('parties') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>
</x-layouts.admin>
