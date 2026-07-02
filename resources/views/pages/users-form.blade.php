@php
    // One view, two modes: $user === null → create, else edit (prefilled).
    $isEdit = $user !== null;
    $title = $isEdit ? 'Edit User' : 'New User';
    // Prefer old() so server-side validation errors repopulate the form.
    $val = fn (string $key, $default = '') => old($key, $user[$key] ?? $default);
    $activeVal = old('is_active', $isEdit ? ($user['is_active'] ? '1' : '0') : '1');
@endphp

<x-layouts.admin :title="$title">
    <x-slot:subtitle>{{ $isEdit ? 'Update ' . $user['name'] . "'s account and role." : 'Create a sign-in and assign a role.' }}</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ route('users') }}">
            <x-ui.icon name="arrow-left" /> Back to users
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card class="mx-auto max-w-3xl">
        <x-ui.card-header>
            <x-ui.card-title>Account Details</x-ui.card-title>
            <x-ui.card-description>Fields marked <span class="text-destructive">*</span> are required.</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            <form class="space-y-5" data-validate method="POST"
                action="{{ $isEdit ? route('users.update', $user['id']) : route('users.store') }}">
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
                        <x-ui.label for="u-name">Full Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="u-name" name="name" value="{{ $val('name') }}" placeholder="e.g. Priya Sharma" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="u-email">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="u-email" name="email" type="email" value="{{ $val('email') }}" placeholder="name@company.com" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="u-password">
                            Password @unless ($isEdit)<span class="text-destructive">*</span>@endunless
                        </x-ui.label>
                        <x-ui.input id="u-password" name="password" type="password"
                            placeholder="{{ $isEdit ? 'Leave blank to keep current' : 'Min. 8 characters' }}"
                            @unless ($isEdit) required @endunless autocomplete="new-password" />
                        @if ($isEdit)
                            <p class="text-xs text-muted-foreground">Leave blank to keep the current password.</p>
                        @endif
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="u-role">Role <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.combobox id="u-role" name="role" width="w-full" placeholder="Select role…" :required="true"
                            :value="$val('role')" :options="['' => 'Select role…'] + $roles" />
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="u-status">Status</x-ui.label>
                        <x-ui.combobox id="u-status" name="is_active" width="w-full" placeholder="Select status…"
                            :value="$activeVal" :options="$statuses" />
                    </div>
                </div>

                <div class="flex gap-2 border-t border-border pt-5">
                    <x-ui.button type="submit" size="sm">
                        <x-ui.icon name="check" /> {{ $isEdit ? 'Update user' : 'Create user' }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" href="{{ route('users') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>
</x-layouts.admin>
