<header class="z-20 flex h-16 shrink-0 items-center gap-4 border-b border-border bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/60 lg:px-6">
    {{-- Mobile: open overlay sidebar --}}
    <x-ui.button data-sidebar-toggle variant="ghost" size="icon" class="lg:hidden">
        <x-ui.icon name="menu" class="size-5" />
    </x-ui.button>
    {{-- Desktop: collapse/expand sidebar rail --}}
    <x-ui.button data-sidebar-collapse variant="ghost" size="icon" class="max-lg:hidden" aria-label="Toggle sidebar">
        <x-ui.icon name="menu" class="size-5" />
    </x-ui.button>

    <div class="ml-auto flex items-center gap-2">
        {{-- Theme toggle --}}
        <x-ui.button data-theme-toggle variant="ghost" size="icon" aria-label="Toggle theme">
            <x-ui.icon name="sun" data-theme-icon="light" class="size-5" />
            <x-ui.icon name="moon" data-theme-icon="dark" class="size-5" hidden />
        </x-ui.button>

        {{-- User menu --}}
        @php
            $user = auth()->user();
            $initials = collect(explode(' ', $user?->name ?? 'User'))
                ->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                ->take(2)->implode('');
        @endphp
        <x-ui.dropdown id="user-menu">
            <x-slot:trigger>
                <x-ui.button variant="ghost" size="icon" class="rounded-full">
                    <x-ui.avatar :initials="$initials" size="size-8" />
                </x-ui.button>
            </x-slot:trigger>

            <div class="px-2 py-1.5">
                <p class="text-sm font-semibold leading-tight">{{ $user?->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $user?->email }}</p>
                @if ($user && $user->roles->isNotEmpty())
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-primary">{{ $user->roles->pluck('name')->implode(', ') }}</p>
                @endif
            </div>
            <x-ui.dropdown-separator />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="relative flex w-full cursor-pointer select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground [&_svg]:size-4 [&_svg]:text-muted-foreground">
                    <x-ui.icon name="logout" /> Log out
                </button>
            </form>
        </x-ui.dropdown>
    </div>
</header>
