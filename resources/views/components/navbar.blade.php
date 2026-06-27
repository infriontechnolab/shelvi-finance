<header class="z-20 flex h-16 shrink-0 items-center gap-4 border-b border-border bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/60 lg:px-6">
    {{-- Mobile: open overlay sidebar --}}
    <x-ui.button data-sidebar-toggle variant="ghost" size="icon" class="lg:hidden">
        <x-ui.icon name="menu" class="size-5" />
    </x-ui.button>
    {{-- Desktop: collapse/expand sidebar rail --}}
    <x-ui.button data-sidebar-collapse variant="ghost" size="icon" class="max-lg:hidden" aria-label="Toggle sidebar">
        <x-ui.icon name="menu" class="size-5" />
    </x-ui.button>

    <div class="relative hidden max-w-sm flex-1 sm:block">
        <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
        <x-ui.input type="search" placeholder="Search…" class="pl-9" />
    </div>

    <div class="ml-auto flex items-center gap-2">
        {{-- Theme toggle --}}
        <x-ui.button data-theme-toggle variant="ghost" size="icon" aria-label="Toggle theme">
            <x-ui.icon name="sun" data-theme-icon="light" class="size-5" />
            <x-ui.icon name="moon" data-theme-icon="dark" class="size-5" hidden />
        </x-ui.button>

        <x-ui.button variant="ghost" size="icon" aria-label="Notifications" class="relative">
            <x-ui.icon name="bell" class="size-5" />
            <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-destructive"></span>
        </x-ui.button>

        {{-- User menu --}}
        <x-ui.dropdown id="user-menu">
            <x-slot:trigger>
                <x-ui.button variant="ghost" size="icon" class="rounded-full">
                    <x-ui.avatar initials="SV" size="size-8" />
                </x-ui.button>
            </x-slot:trigger>

            <div class="px-2 py-1.5 text-sm font-semibold">My Account</div>
            <x-ui.dropdown-separator />
            <x-ui.dropdown-item href="#"><x-ui.icon name="user" /> Profile</x-ui.dropdown-item>
            <x-ui.dropdown-item href="#"><x-ui.icon name="settings" /> Settings</x-ui.dropdown-item>
            <x-ui.dropdown-separator />
            <x-ui.dropdown-item href="#"><x-ui.icon name="logout" /> Log out</x-ui.dropdown-item>
        </x-ui.dropdown>
    </div>
</header>
