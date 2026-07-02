<x-layouts.admin title="Dashboard">
    <x-slot:subtitle>Welcome back — here's your finance snapshot for today.</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ route('reports') }}">Reports</x-ui.button>
        <x-ui.button size="sm" href="{{ route('money-received') }}"><x-ui.icon name="plus" /> New entry</x-ui.button>
    </x-slot:actions>

    {{-- KPI tiles --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($kpis as $k)
            <x-ui.card>
                <x-ui.card-header class="flex-row items-center justify-between space-y-0 pb-2">
                    <x-ui.card-description>{{ $k['label'] }}</x-ui.card-description>
                    <x-ui.icon :name="$k['up'] ? 'trending-up' : 'trending-down'"
                        class="size-4 {{ $k['up'] ? 'text-emerald-500' : 'text-destructive' }}" />
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="num font-mono text-2xl font-bold tabular-nums {{ ($k['value'] < 0) ? 'text-destructive' : '' }}">
                        {{ ($k['isCount'] ?? false) ? $k['value'] : \App\Support\Inr::format($k['value']) }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        <span class="{{ $k['up'] ? 'text-emerald-600' : 'text-destructive' }}">{{ $k['trend'] }}</span>
                        vs last period
                    </p>
                </x-ui.card-content>
            </x-ui.card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-7">
        {{-- Weekly collections vs payments --}}
        @php $chartMax = collect($weeklyChart)->flatMap(fn ($w) => [$w['collection'], $w['payment']])->max(); @endphp
        <x-ui.card class="lg:col-span-4">
            <x-ui.card-header>
                <x-ui.card-title>Collections vs Payments</x-ui.card-title>
                <x-ui.card-description>Weekly inflow and outflow this month.</x-ui.card-description>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="flex h-64 items-stretch gap-6">
                    @foreach ($weeklyChart as $w)
                        <div class="flex h-full flex-1 flex-col items-center gap-2">
                            <div class="flex w-full flex-1 items-end justify-center gap-1.5">
                                <div class="w-1/2 rounded-t-md bg-emerald-500/80 transition-all hover:bg-emerald-500"
                                    style="height: {{ round($w['collection'] / $chartMax * 100) }}%"
                                    title="Collection: {{ \App\Support\Inr::format($w['collection']) }}"></div>
                                <div class="w-1/2 rounded-t-md bg-primary/80 transition-all hover:bg-primary"
                                    style="height: {{ round($w['payment'] / $chartMax * 100) }}%"
                                    title="Payment: {{ \App\Support\Inr::format($w['payment']) }}"></div>
                            </div>
                            <span class="text-xs text-muted-foreground">{{ $w['week'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center justify-center gap-6 text-xs text-muted-foreground">
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-emerald-500/80"></span> Collections</span>
                    <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-primary/80"></span> Payments</span>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        {{-- Cheques pending verification --}}
        <x-ui.card class="lg:col-span-3">
            <x-ui.card-header class="flex-row items-center justify-between space-y-0">
                <div class="space-y-1.5">
                    <x-ui.card-title>Pending Verification</x-ui.card-title>
                    <x-ui.card-description>Cheques awaiting clearance.</x-ui.card-description>
                </div>
                <x-ui.button variant="outline" size="sm" href="{{ route('cheques') }}">View all</x-ui.button>
            </x-ui.card-header>
            <x-ui.card-content>
                <ul class="divide-y divide-border">
                    @foreach ($pending as $p)
                        <li class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-semibold text-amber-600">
                                    {{ strtoupper(substr($p['party'], 0, 1)) }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium">{{ $p['party'] }}</p>
                                    <p class="text-xs text-muted-foreground">{{ \App\Support\Dates::human($p['date']) }}</p>
                                </div>
                            </div>
                            <span class="num font-mono text-sm font-semibold tabular-nums">{{ \App\Support\Inr::format($p['amount']) }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    {{-- Recent transactions --}}
    <div id="recent-txns-table-pagelen" hidden>
        <x-ui.combobox id="recentTxnsPageLen" value="8" width="w-32" searchPlaceholder="Rows…"
            :options="['8' => '8 rows', '15' => '15 rows', '25' => '25 rows', '50' => '50 rows']" />
    </div>

    <x-ui.card>
        <x-ui.card-header class="flex-row items-center justify-between space-y-0">
            <div class="space-y-1.5">
                <x-ui.card-title>Recent Transactions</x-ui.card-title>
                <x-ui.card-description>Latest money movements across all bank accounts.</x-ui.card-description>
            </div>
        </x-ui.card-header>
        <x-ui.card-content>
            {{ $txnsTable->table(['class' => 'w-full text-sm']) }}
        </x-ui.card-content>
    </x-ui.card>

    @push('scripts')
        {{ $txnsTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-layouts.admin>
