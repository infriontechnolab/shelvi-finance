@php
    $describe = fn (string $name) => match ($name) {
        'admin' => 'Full access to every resource — except user & role administration.',
        'accountant' => 'Day-to-day operations — no deletes, no administration.',
        default => 'Custom role.',
    };
    $builtin = ['admin', 'accountant'];
@endphp

<x-layouts.admin title="Roles & Permissions">
    <x-slot:subtitle>Control what each role can see and do.</x-slot:subtitle>
    <x-slot:actions>
        @can('roles.create')
            <x-ui.button size="sm" href="{{ route('roles.create') }}">
                <x-ui.icon name="plus" /> New role
            </x-ui.button>
        @endcan
    </x-slot:actions>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($roles as $role)
            <x-ui.card>
                <x-ui.card-content class="pt-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <x-ui.icon name="{{ $role->name === 'admin' ? 'shield-check' : 'user' }}" class="size-5" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold capitalize leading-tight">{{ $role->name }}</p>
                                <p class="text-xs text-muted-foreground">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</p>
                            </div>
                        </div>
                        @unless (in_array($role->name, $builtin, true))
                            <span class="inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-[11px] font-semibold text-muted-foreground">Custom</span>
                        @endunless
                    </div>

                    <p class="mt-4 text-sm text-muted-foreground">{{ $describe($role->name) }}</p>

                    <div class="mt-4 flex items-center justify-between border-t border-border pt-3">
                        <span class="text-xs text-muted-foreground">{{ $role->permissions_count }} permissions</span>
                        <div class="flex items-center gap-1">
                            @can('roles.delete')
                                @if (! in_array($role->name, $builtin, true) && $role->users_count === 0)
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" data-delete-form>
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" variant="ghost" size="sm"
                                            data-confirm="Delete the &ldquo;{{ $role->name }}&rdquo; role?"
                                            class="text-destructive hover:text-destructive">
                                            <x-ui.icon name="trash-2" />
                                        </x-ui.button>
                                    </form>
                                @endif
                            @endcan
                            @can('roles.update')
                                <x-ui.button variant="outline" size="sm" href="{{ route('roles.edit', $role) }}">
                                    <x-ui.icon name="settings" /> Edit
                                </x-ui.button>
                            @endcan
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endforeach
    </div>
</x-layouts.admin>
