<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify code — Shelvi Finance</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen">

        {{-- ───────────── Left: OTP form ───────────── --}}
        <div class="relative flex w-full flex-col justify-between overflow-hidden bg-white p-6 sm:p-10 lg:w-1/2 lg:p-14">
            @php
                $watermarkIcons = ['landmark', 'wallet', 'trending-up', 'file-text', 'book-open', 'arrow-left-right', 'shield-check', 'users', 'calendar', 'file-spreadsheet'];
                $rowTops = [6, 22, 38, 54, 70, 86];
                $colLefts = [3, 13, 23, 33, 43, 53, 63, 73, 83, 93];
            @endphp
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 select-none overflow-hidden text-slate-900/[0.06]">
                @foreach ($rowTops as $r => $top)
                    @php $shift = $r % 2 === 0 ? 0 : 5; @endphp
                    @foreach ($colLefts as $c => $left)
                        <i data-lucide="{{ $watermarkIcons[$c] }}"
                            class="absolute size-5"
                            style="top: {{ $top }}%; left: {{ min(95, $left + $shift) }}%; transform: rotate({{ (($r * 10 + $c) * 17) % 40 - 20 }}deg);"></i>
                    @endforeach
                @endforeach
            </div>

            {{-- Brand --}}
            <a href="{{ route('login') }}" class="relative flex items-center gap-2.5">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white p-1 shadow-sm ring-1 ring-border">
                    <img src="/logo.svg" alt="Shelvi" class="size-full">
                </span>
                <span class="font-display text-lg font-bold tracking-tight">Shelvi</span>
            </a>

            {{-- Form (vertically centered) --}}
            <div class="relative mx-auto w-full max-w-sm py-10">
                <h1 class="font-display text-3xl font-bold tracking-tight">Check your email</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Enter the 4-digit code we sent for <span class="font-medium text-foreground">{{ $email }}</span>.
                </p>

                @if (session('status'))
                    <div class="mt-4 rounded-md bg-emerald-500/10 px-3 py-2 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('otp.verify.store') }}" class="mt-8 space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <x-ui.label for="code">Verification code</x-ui.label>
                        <x-ui.input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]*"
                            maxlength="4" placeholder="0000" required autofocus autocomplete="one-time-code"
                            class="text-center text-2xl font-mono tracking-[0.5em] user-invalid:border-destructive" />
                        @error('code')
                            <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" class="w-full">Verify &amp; sign in</x-ui.button>
                </form>

                <form method="POST" action="{{ route('otp.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-primary hover:underline">
                        Didn't get a code? Resend
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-muted-foreground">
                    <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Back to login</a>
                </p>
            </div>

            {{-- Footer --}}
            <div class="relative flex flex-wrap items-center justify-between gap-2 text-xs text-muted-foreground">
                <span>© {{ date('Y') }} Shelvi Finance</span>
                <span class="inline-flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="size-3.5"></i> Secure sign-in
                </span>
            </div>
        </div>

        {{-- ───────────── Right: brand panel ───────────── --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-[#16233F] via-[#1E3A66] to-[#2C2359] p-14 text-white lg:flex lg:flex-col lg:items-center lg:justify-center">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.10]"
                style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 22px 22px;"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-black/20"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -left-16 size-80 rounded-full bg-black/20 blur-3xl"></div>

            <div class="relative flex max-w-md flex-col items-center text-center">
                <span class="flex size-16 items-center justify-center rounded-2xl bg-white p-2 shadow-lg">
                    <img src="/logo.svg" alt="Shelvi" class="size-full">
                </span>
                <h2 class="mt-6 font-display text-3xl font-bold leading-tight tracking-tight text-white">Two-step verification</h2>
                <p class="mt-3 text-sm leading-relaxed text-white/95">
                    We emailed a 4-digit code to confirm it's really you before opening your dashboard.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
