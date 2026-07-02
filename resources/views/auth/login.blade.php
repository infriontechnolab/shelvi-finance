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

        {{-- ───────────── Right: brand panel + dashboard preview ───────────── --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-primary p-14 text-primary-foreground lg:flex lg:flex-col lg:justify-center">
            {{-- soft background flourishes --}}
            <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-white/10 blur-2xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-16 size-80 rounded-full bg-black/10 blur-2xl"></div>

            <div class="relative max-w-md">
                <h2 class="font-display text-3xl font-bold leading-tight tracking-tight">
                    Receivables, banks &amp; cheques — in one place.
                </h2>
                <p class="mt-3 text-sm text-primary-foreground/80">
                    Sign in to track collections, payments and party ledgers across every account.
                </p>

                {{-- Dashboard preview (decorative) --}}
                <div aria-hidden="true" class="mt-10 space-y-3 rounded-2xl bg-white/10 p-3 backdrop-blur-sm ring-1 ring-white/15">
                    {{-- KPI tiles --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-card p-3 text-card-foreground shadow-sm">
                            <p class="text-[11px] text-muted-foreground">Total Bank Balance</p>
                            <p class="num mt-1 font-mono text-lg font-bold tabular-nums">₹55,37,600</p>
                            <p class="mt-0.5 inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600">
                                <i data-lucide="trending-up" class="size-3"></i> +2.4%
                            </p>
                        </div>
                        <div class="rounded-xl bg-card p-3 text-card-foreground shadow-sm">
                            <p class="text-[11px] text-muted-foreground">Amount to Receive</p>
                            <p class="num mt-1 font-mono text-lg font-bold tabular-nums">₹12,67,000</p>
                            <p class="mt-0.5 inline-flex items-center gap-1 text-[11px] font-medium text-sky-600">
                                <i data-lucide="wallet" class="size-3"></i> 6 parties
                            </p>
                        </div>
                    </div>

                    {{-- Mini weekly chart --}}
                    <div class="rounded-xl bg-card p-3 text-card-foreground shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-[11px] text-muted-foreground">Weekly Collections</p>
                            <span class="text-[11px] font-medium text-muted-foreground">This month</span>
                        </div>
                        <div class="mt-3 flex h-16 items-end gap-2">
                            @foreach (['45', '70', '58', '90'] as $h)
                                <div class="flex-1 rounded-t bg-primary/80" style="height: {{ $h }}%"></div>
                            @endforeach
                        </div>
                        <div class="mt-1 flex justify-between text-[10px] text-muted-foreground">
                            <span>W1</span><span>W2</span><span>W3</span><span>W4</span>
                        </div>
                    </div>

                    {{-- Recent transactions --}}
                    <div class="rounded-xl bg-card p-3 text-card-foreground shadow-sm">
                        <p class="mb-2 text-[11px] text-muted-foreground">Recent Transactions</p>
                        <div class="space-y-2">
                            @foreach ([['Mehta Traders', '+₹85,000', 'text-emerald-600'], ['Patel Enterprises', '−₹32,000', 'text-rose-600'], ['Rajesh &amp; Co', '+₹1,20,000', 'text-emerald-600']] as [$party, $amt, $tone])
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="flex items-center gap-2">
                                        <span class="flex size-5 items-center justify-center rounded-full bg-primary/10 text-[9px] font-semibold text-primary">
                                            {{ mb_substr($party, 0, 1) }}
                                        </span>
                                        {!! $party !!}
                                    </span>
                                    <span class="num font-mono font-semibold tabular-nums {{ $tone }}">{{ $amt }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
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
