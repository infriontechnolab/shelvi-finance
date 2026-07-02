@php
    // One view, two modes: $role === null → create, else edit.
    $creating = $role === null;
    $assignedSet = collect($assigned);
    $actionLabel = fn (string $perm) => ucfirst(explode('.', $perm)[1] ?? $perm);
    $groupLabel = fn (string $key) => ucfirst(str_replace('_', ' ', $key));
    $icons = [
        'dashboard' => 'dashboard', 'banks' => 'landmark', 'parties' => 'users',
        'transactions' => 'arrow-left-right', 'ledger' => 'book-open', 'cheques' => 'wallet',
        'reports' => 'file-text',
    ];
    // Aligned columns for the common CRUD actions; anything else (verify, export)
    // falls into the trailing "Other" cell so rows still line up.
    $core = ['view', 'create', 'update', 'delete'];
    $coreLabels = ['view' => 'View', 'create' => 'Create', 'update' => 'Update', 'delete' => 'Delete'];
    $actionOf = fn ($p) => explode('.', $p->name)[1] ?? $p->name;
    $isChecked = fn ($p) => $assignedSet->contains($p->name);
    $total = collect($groups)->flatten()->count();
    $selected = $assignedSet->count();
    $heading = $creating ? 'New role' : ucfirst($role->name).' role';
    $describe = $creating
        ? 'Name the role and pick what it can do.'
        : match ($role->name) {
            'admin' => 'Full access — except user & role administration.',
            'accountant' => 'Day-to-day operations — no administration.',
            default => 'Custom role.',
        };
@endphp

<x-layouts.admin title="{{ $heading }}">
    <x-slot:subtitle>{{ $describe }}</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="ghost" size="sm" href="{{ route('roles') }}">
            <x-ui.icon name="arrow-left" /> Back
        </x-ui.button>
    </x-slot:actions>

    <form method="POST" action="{{ $creating ? route('roles.store') : route('roles.update', $role) }}"
        data-role-form {{ $creating ? 'data-validate' : '' }}>
        @csrf
        @unless ($creating) @method('PUT') @endunless

        @if ($errors->any())
            <div class="mb-5 rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($creating)
            <x-ui.card class="mb-5">
                <x-ui.card-content class="pt-6">
                    <div class="max-w-sm space-y-1.5">
                        <x-ui.label for="role-name">Role name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="role-name" name="name" value="{{ old('name') }}" placeholder="e.g. auditor" required
                            class="user-invalid:border-destructive user-invalid:ring-destructive/20" />
                        <p class="text-xs text-muted-foreground">Lowercase, one word. Must be unique.</p>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endif

        <x-ui.card class="overflow-hidden">
            {{-- Toolbar: live count + bulk actions --}}
            <div class="flex items-center justify-between gap-4 border-b border-border px-6 py-4">
                <p class="text-sm text-muted-foreground">
                    <span data-selected-count class="font-semibold text-foreground">{{ $selected }}</span>
                    of {{ $total }} permissions
                </p>
                <div class="flex items-center gap-1">
                    <x-ui.button type="button" variant="ghost" size="sm" data-select-all>Select all</x-ui.button>
                    <x-ui.button type="button" variant="ghost" size="sm" data-clear-all>Clear</x-ui.button>
                </div>
            </div>

            {{-- Aligned permission matrix --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[46rem] text-sm">
                    <thead>
                        <tr class="border-b border-border text-xs font-medium text-muted-foreground">
                            <th class="py-3 pl-6 pr-4 text-left">Resource</th>
                            @foreach ($core as $a)
                                <th class="w-24 px-3 py-3 text-center">{{ $coreLabels[$a] }}</th>
                            @endforeach
                            <th class="px-3 py-3 pr-6 text-left">Other</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($groups as $key => $permissions)
                            @php
                                $byAction = $permissions->keyBy($actionOf);
                                $extras = $permissions->reject(fn ($p) => in_array($actionOf($p), $core, true));
                                $groupSelected = $permissions->filter($isChecked)->count();
                            @endphp
                            <tr class="transition-colors hover:bg-muted/30" data-group="{{ $key }}">
                                <td class="py-3 pl-6 pr-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                            <x-ui.icon name="{{ $icons[$key] ?? 'settings' }}" class="size-4" />
                                        </span>
                                        <div>
                                            <p class="font-medium text-foreground">{{ $groupLabel($key) }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                <span data-group-count>{{ $groupSelected }}</span> of {{ $permissions->count() }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                @foreach ($core as $a)
                                    <td class="px-3 py-3 text-center">
                                        @if ($p = $byAction->get($a))
                                            <div class="flex justify-center">
                                                <x-ui.switch name="permissions[]" :value="$p->name"
                                                    :checked="$isChecked($p)" data-perm />
                                            </div>
                                        @else
                                            <span class="text-muted-foreground/30">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-3 py-3 pr-6">
                                    @if ($extras->isNotEmpty())
                                        <div class="flex flex-wrap gap-4">
                                            @foreach ($extras as $p)
                                                <x-ui.switch name="permissions[]" :value="$p->name" :label="$actionLabel($p->name)"
                                                    :checked="$isChecked($p)" data-perm />
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted-foreground/30">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- Floating save bar --}}
        <div class="pointer-events-none sticky bottom-4 z-10 mt-6 flex justify-center">
            <div class="pointer-events-auto flex items-center gap-3 rounded-full border border-border bg-card/95 px-3 py-2 shadow-lg backdrop-blur">
                <span class="pl-2 text-sm text-muted-foreground">
                    <span data-selected-count class="font-semibold text-foreground">{{ $selected }}</span> selected
                </span>
                <x-ui.button type="submit" size="sm"><x-ui.icon name="check" /> {{ $creating ? 'Create role' : 'Save' }}</x-ui.button>
                <x-ui.button type="button" variant="ghost" size="sm" href="{{ route('roles') }}">Cancel</x-ui.button>
            </div>
        </div>
    </form>

    @push('scripts')
        <script type="module">
            const form = document.querySelector('[data-role-form]');
            if (form) {
                const perms = () => [...form.querySelectorAll('[data-perm]')];
                const refresh = () => {
                    const total = perms().filter(c => c.checked).length;
                    form.querySelectorAll('[data-selected-count]').forEach(el => el.textContent = total);
                    form.querySelectorAll('[data-group]').forEach(group => {
                        const n = [...group.querySelectorAll('[data-perm]')].filter(c => c.checked).length;
                        group.querySelector('[data-group-count]').textContent = n;
                    });
                };
                form.addEventListener('change', e => { if (e.target.matches('[data-perm]')) refresh(); });
                form.querySelector('[data-select-all]')?.addEventListener('click', () => {
                    perms().forEach(c => c.checked = true); refresh();
                });
                form.querySelector('[data-clear-all]')?.addEventListener('click', () => {
                    perms().forEach(c => c.checked = false); refresh();
                });
                refresh();
            }
        </script>
    @endpush
</x-layouts.admin>
