@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Shelvi Admin</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Brand fonts (Manrope + Plus Jakarta Sans) self-hosted via laravel-vite-plugin
         bunny() — bundled into public/build, no runtime CDN needed on shared hosting. --}}

    {{-- No-flash theme boot: set .dark before paint, before CSS loads --}}
    <script>
        (function () {
            try {
                var k = 'shelvi-theme', s = localStorage.getItem(k);
                var dark = s === 'dark' || (!s && matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-background font-sans text-foreground antialiased">
    <div class="flex h-screen">
        {{-- Mobile backdrop --}}
        <div data-sidebar-backdrop class="fixed inset-0 z-30 hidden bg-black/50 lg:hidden"></div>

        <x-sidebar />

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <x-navbar />

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h1 class="font-display text-2xl font-bold tracking-tight">{{ $title }}</h1>
                            @isset($subtitle)
                                <p class="text-sm text-muted-foreground">{{ $subtitle }}</p>
                            @endisset
                        </div>
                        @isset($actions)
                            <div class="flex items-center gap-2">{{ $actions }}</div>
                        @endisset
                    </div>

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-confirm-dialog />
    <x-toast />

    {{-- DataTables (and other per-page) scripts inject here --}}
    @stack('scripts')
</body>
</html>
