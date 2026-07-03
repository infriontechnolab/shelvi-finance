<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Shelvi Finance</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- No-flash theme boot (same as admin shell) --}}
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
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    <div class="flex min-h-screen">

        {{-- ───────────── Left: sign-in form ───────────── --}}
        <div class="flex w-full flex-col justify-between p-6 sm:p-10 lg:w-1/2 lg:p-14">
            {{-- Brand --}}
            <a href="{{ route('login') }}" class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-lg bg-primary text-base font-bold text-primary-foreground">S</span>
                <span class="font-display text-lg font-bold tracking-tight">Shelvi</span>
            </a>

            {{-- Form (vertically centered) --}}
            <div class="mx-auto w-full max-w-sm py-10">
                <h1 class="font-display text-3xl font-bold tracking-tight">Welcome back</h1>
                <p class="mt-2 text-sm text-muted-foreground">Enter your email and password to access your dashboard.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-md bg-emerald-500/10 px-3 py-2 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <x-ui.label for="email">Email</x-ui.label>
                        <x-ui.input type="email" name="email" id="email" value="{{ old('email') }}"
                            placeholder="you@company.com" required autofocus autocomplete="username"
                            class="user-invalid:border-destructive" />
                        @error('email')
                            <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <x-ui.label for="password">Password</x-ui.label>
                        <div class="relative">
                            <x-ui.input type="password" name="password" id="password"
                                placeholder="••••••••" required autocomplete="current-password" class="pr-10" />
                            <button type="button" id="toggle-password" tabindex="-1"
                                aria-label="Show password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground transition-colors hover:text-foreground">
                                <span data-eye="show"><i data-lucide="eye" class="size-4"></i></span>
                                <span data-eye="hide" hidden><i data-lucide="eye-off" class="size-4"></i></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-muted-foreground">
                        <input type="checkbox" name="remember" value="1"
                            class="size-4 rounded border-input text-primary focus:ring-2 focus:ring-ring">
                        Remember me
                    </label>

                    <x-ui.button type="submit" class="w-full">Log in</x-ui.button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
                <span>© {{ date('Y') }} Shelvi Finance</span>
                <span class="inline-flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="size-3.5"></i> Secure sign-in
                </span>
            </div>
        </div>

        {{-- ───────────── Right: brand panel ───────────── --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-[#D9531A] via-[#B23A1E] to-[#241C4A] p-14 text-white lg:flex lg:flex-col lg:items-center lg:justify-center">
            {{-- subtle dot texture + soft flourishes --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.10]"
                style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 22px 22px;"></div>
            {{-- dark scrim keeps white text high-contrast over the bright areas --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-black/20"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-16 size-80 rounded-full bg-black/20 blur-3xl"></div>

            <div class="relative flex max-w-md flex-col items-center text-center">
                <span class="flex size-16 items-center justify-center rounded-2xl bg-white p-2 shadow-lg">
                    <img src="/logo.svg" alt="Shelvi" class="size-full">
                </span>
                <h2 class="mt-6 font-display text-3xl font-bold leading-tight tracking-tight text-white">Shelvi Finance</h2>
                <p class="mt-3 text-sm leading-relaxed text-white/95">
                    Track collections, payments and party ledgers across every account — banks, receivables and cheques in one place.
                </p>

                <div class="mt-10 grid w-full max-w-xs gap-3 text-left">
                    @foreach ([['landmark', 'Bank accounts & statements'], ['users', 'Party ledgers & balances'], ['wallet', 'Cheque tracking & clearing'], ['file-text', 'Reports with CSV / PDF export']] as [$icon, $text])
                        <div class="flex items-center gap-3 rounded-xl bg-white/15 px-4 py-2.5 ring-1 ring-white/20 backdrop-blur-sm">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/20">
                                <i data-lucide="{{ $icon }}" class="size-4 text-white"></i>
                            </span>
                            <span class="text-sm font-medium text-white">{{ $text }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Password visibility toggle --}}
    <script>
        (function () {
            var pw = document.getElementById('password');
            var btn = document.getElementById('toggle-password');
            if (!pw || !btn) return;
            btn.addEventListener('click', function () {
                var show = pw.type === 'password';
                pw.type = show ? 'text' : 'password';
                btn.querySelector('[data-eye="show"]').hidden = show;
                btn.querySelector('[data-eye="hide"]').hidden = !show;
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        })();
    </script>
</body>
</html>
