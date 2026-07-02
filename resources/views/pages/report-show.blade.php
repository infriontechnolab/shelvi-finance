@php
    $periodLabels = [
        'all' => 'All time', 'today' => 'Today', 'week' => 'This week',
        'month' => 'This month', 'quarter' => 'This quarter', 'year' => 'This year',
    ];
    // Outstanding is a live snapshot — the period filter doesn't apply.
    $showPeriods = $slug !== 'outstanding';
@endphp

<x-layouts.admin title="{{ $title }}">
    <x-slot:subtitle>{{ $desc }} · {{ $periodLabel }}</x-slot:subtitle>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" href="{{ route('reports') }}">
            <x-ui.icon name="arrow-left" /> All reports
        </x-ui.button>
    </x-slot:actions>

    @if ($showPeriods)
        <div class="mb-4 flex flex-wrap items-center gap-1.5">
            @foreach ($periods as $p)
                <a href="{{ route('reports.show', ['report' => $slug, 'period' => $p]) }}"
                    class="inline-flex items-center rounded-md px-3 py-1.5 text-xs font-medium transition-colors
                        {{ $p === $period ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                    {{ $periodLabels[$p] }}
                </a>
            @endforeach
        </div>
    @endif

    <x-ui.card class="overflow-hidden">
        <x-ui.card-content class="p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-xs font-medium text-muted-foreground">
                            @foreach ($columns as $col)
                                <th class="px-4 py-3 {{ $col['align'] === 'right' ? 'text-right' : 'text-left' }}">{{ $col['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($rows as $row)
                            <tr class="transition-colors hover:bg-muted/30">
                                @foreach ($row as $i => $cell)
                                    <td class="px-4 py-3 {{ $columns[$i]['align'] === 'right' ? 'text-right font-mono tabular-nums' : 'text-left' }} {{ $i === 0 ? 'font-medium text-foreground' : 'text-muted-foreground' }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                    No entries for {{ strtolower($periodLabel) }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card-content>
    </x-ui.card>
</x-layouts.admin>
