@php
    // Nav comes from config/navigation.php (data, not markup). Items (and children)
    // may carry a `permission`; those are hidden from users who lack it.
    $me = auth()->user();
    $canSee = fn ($item) => empty($item['permission']) || $me?->can($item['permission']);
    $nav = collect(config('navigation', []))
        ->filter($canSee)
        ->map(function ($item) use ($canSee) {
            if (! empty($item['children'])) {
                $item['children'] = collect($item['children'])->filter($canSee)->values()->all();
            }

            return $item;
        })
        ->reject(fn ($item) => isset($item['children']) && count($item['children']) === 0 && ! isset($item['route']))
        ->values();
    $childActive = fn ($item) => collect($item['children'] ?? [])->contains(fn ($c) => request()->routeIs($c['route']));
    $rowBase = 'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors group-data-[collapsed=true]/sidebar:lg:justify-center group-data-[collapsed=true]/sidebar:lg:px-0';
@endphp

<aside data-sidebar data-collapsed="false"
    class="group/sidebar fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-all duration-200 lg:static lg:translate-x-0 data-[collapsed=true]:lg:w-16">
    <div class="flex h-16 items-center gap-2 border-b border-sidebar-border px-6 group-data-[collapsed=true]/sidebar:lg:justify-center group-data-[collapsed=true]/sidebar:lg:px-0">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-white p-0.5 shadow-sm">
            <img src="/logo.svg" alt="Shelvi" class="size-full">
        </span>
        <span class="font-display text-lg font-bold tracking-tight group-data-[collapsed=true]/sidebar:lg:hidden">Shelvi</span>
    </div>

    {{-- overflow-visible when collapsed so hover flyouts can escape the rail --}}
    <nav class="flex-1 space-y-1 overflow-y-auto p-3 group-data-[collapsed=true]/sidebar:lg:overflow-visible">
        @foreach ($nav as $item)
            @php $active = isset($item['route']) ? request()->routeIs($item['route']) : $childActive($item); @endphp

            {{-- group/item drives the collapsed hover-flyout --}}
            <div class="group/item relative">
                @if (!empty($item['children']))
                    {{-- Expandable group (inline accordion when expanded) --}}
                    <details class="group/col" @if ($active) open @endif>
                        <summary @class([
                            $rowBase,
                            'cursor-pointer list-none [&::-webkit-details-marker]:hidden',
                            'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
                            'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => !$active,
                        ])>
                            <x-ui.icon :name="$item['icon']" class="size-4 shrink-0" />
                            <span class="flex-1 group-data-[collapsed=true]/sidebar:lg:hidden">{{ $item['label'] }}</span>
                            <x-ui.icon name="chevron-down" class="size-4 shrink-0 transition-transform group-open/col:rotate-180 group-data-[collapsed=true]/sidebar:lg:hidden" />
                        </summary>
                        <div class="mt-1 space-y-1 pl-9 group-data-[collapsed=true]/sidebar:lg:hidden">
                            @foreach ($item['children'] as $child)
                                @php $cActive = request()->routeIs($child['route']); @endphp
                                <a href="{{ route($child['route']) }}"
                                    @class([
                                        'block rounded-md px-3 py-1.5 text-sm transition-colors',
                                        'text-sidebar-accent-foreground font-medium' => $cActive,
                                        'text-sidebar-foreground/60 hover:text-sidebar-accent-foreground' => !$cActive,
                                    ])>{{ $child['label'] }}</a>
                            @endforeach
                        </div>
                    </details>
                @else
                    {{-- Leaf link --}}
                    <a href="{{ route($item['route']) }}"
                        @class([
                            $rowBase,
                            'bg-sidebar-accent text-sidebar-accent-foreground' => $active,
                            'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' => !$active,
                        ])>
                        <x-ui.icon :name="$item['icon']" class="size-4 shrink-0" />
                        <span class="group-data-[collapsed=true]/sidebar:lg:hidden">{{ $item['label'] }}</span>
                    </a>
                @endif

                {{-- Hover flyout: only when sidebar collapsed (desktop). pl-2 = hover bridge --}}
                <div class="absolute left-full top-0 z-50 hidden pl-2 lg:group-data-[collapsed=true]/sidebar:group-hover/item:block">
                    <div class="min-w-44 rounded-md border border-border bg-popover p-1 text-popover-foreground shadow-md">
                        @if (!empty($item['children']))
                            <div class="px-2 py-1.5 text-sm font-semibold">{{ $item['label'] }}</div>
                            @foreach ($item['children'] as $child)
                                <a href="{{ route($child['route']) }}"
                                    class="block rounded-sm px-2 py-1.5 text-sm transition-colors hover:bg-accent hover:text-accent-foreground">{{ $child['label'] }}</a>
                            @endforeach
                        @else
                            <a href="{{ route($item['route']) }}"
                                class="block rounded-sm px-2 py-1.5 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground">{{ $item['label'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-sidebar-border p-3">
        @php
            $initials = collect(explode(' ', $me?->name ?? 'User'))
                ->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
        @endphp
        <div class="flex items-center gap-3 rounded-md px-3 py-2 group-data-[collapsed=true]/sidebar:lg:justify-center group-data-[collapsed=true]/sidebar:lg:px-0">
            <x-ui.avatar :initials="$initials" />
            <div class="min-w-0 flex-1 group-data-[collapsed=true]/sidebar:lg:hidden">
                <p class="truncate text-sm font-medium">{{ $me?->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $me?->email }}</p>
            </div>
        </div>
    </div>
</aside>
