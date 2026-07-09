<?php

namespace App\Repositories\Eloquent;

use App\Models\LedgerEntry;
use App\Models\Party;
use App\Models\Transaction;
use App\Repositories\Contracts\ReportRepository;
use App\Support\Dates;
use App\Support\Inr;
use Illuminate\Support\Carbon;

/**
 * Database-backed reports. Each report is a slug → table (columns + rows).
 * Grouping/aggregation is done in PHP over small result sets so the queries
 * stay portable (no DB-specific date functions).
 */
class EloquentReportRepository implements ReportRepository
{
    private const CATALOGUE = [
        ['slug' => 'daily-collection', 'title' => 'Daily Collection Report', 'desc' => 'Day-wise inflow summary across banks', 'icon' => 'trending-up'],
        ['slug' => 'daily-payment', 'title' => 'Daily Payment Report', 'desc' => 'Day-wise outflow summary across banks', 'icon' => 'trending-down'],
        ['slug' => 'monthly-summary', 'title' => 'Monthly Summary Report', 'desc' => 'Comprehensive monthly performance overview', 'icon' => 'calendar'],
        ['slug' => 'bank-wise', 'title' => 'Bank-wise Report', 'desc' => 'Inflow and outflow segregated per bank', 'icon' => 'landmark'],
        ['slug' => 'party-wise', 'title' => 'Party-wise Report', 'desc' => 'Detailed activity per party', 'icon' => 'users'],
        ['slug' => 'outstanding', 'title' => 'Outstanding Report', 'desc' => 'Receivables and payables pending', 'icon' => 'alert-circle'],
        ['slug' => 'credit', 'title' => 'Credit Report', 'desc' => 'All credit entries across the ledger', 'icon' => 'arrow-down-left'],
        ['slug' => 'debit', 'title' => 'Debit Report', 'desc' => 'All debit entries across the ledger', 'icon' => 'arrow-up-right'],
        ['slug' => 'ledger', 'title' => 'Ledger Report', 'desc' => 'Full general ledger by date', 'icon' => 'book-open'],
    ];

    public function types(): array
    {
        return self::CATALOGUE;
    }

    public function generate(string $slug, string $period): ?array
    {
        $type = collect(self::CATALOGUE)->firstWhere('slug', $slug);
        if (! $type) {
            return null;
        }

        // Outstanding is a live snapshot; every other report honours the period.
        $range = $slug === 'outstanding' ? null : $this->range($period);

        [$columns, $rows] = match ($slug) {
            'daily-collection' => $this->daily('received', $range),
            'daily-payment' => $this->daily('paid', $range),
            'monthly-summary' => $this->monthly($range),
            'bank-wise' => $this->bankWise($range),
            'party-wise' => $this->partyWise($range),
            'outstanding' => $this->outstanding(),
            'credit' => $this->ledgerSide('credit', $range),
            'debit' => $this->ledgerSide('debit', $range),
            'ledger' => $this->ledgerAll($range),
        };

        return [
            'title' => $type['title'],
            'desc' => $type['desc'],
            'icon' => $type['icon'],
            'periodLabel' => $slug === 'outstanding' ? 'As of today' : $this->periodLabel($period),
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    // ---- Report builders --------------------------------------------------

    private function daily(string $direction, ?array $range): array
    {
        $rows = Transaction::query()->where('direction', $direction)
            ->when($range, fn ($q) => $q->whereDate('txn_date', '>=', $range[0])->whereDate('txn_date', '<=', $range[1]))
            ->get()
            ->groupBy(fn ($t) => $t->txn_date->format('Y-m-d'))
            ->map(fn ($g, $date) => [$date, $g->count(), (int) $g->sum('amount')])
            ->sortByDesc(fn ($r) => $r[0])->values()
            ->map(fn ($r) => [Dates::human($r[0]), (string) $r[1], $this->rupees($r[2])])->all();

        return [[$this->col('Date'), $this->col('Entries', 'right'), $this->col('Amount', 'right')], $rows];
    }

    private function monthly(?array $range): array
    {
        $rows = Transaction::query()
            ->when($range, fn ($q) => $q->whereDate('txn_date', '>=', $range[0])->whereDate('txn_date', '<=', $range[1]))
            ->get()
            ->groupBy(fn ($t) => $t->txn_date->format('Y-m'))
            ->map(function ($g, $ym) {
                $in = (int) $g->where('direction', 'received')->sum('amount');
                $out = (int) $g->where('direction', 'paid')->sum('amount');

                return [$ym, $in, $out, $in - $out];
            })
            ->sortByDesc(fn ($r) => $r[0])->values()
            ->map(fn ($r) => [
                Carbon::parse($r[0].'-01')->format('M Y'),
                $this->rupees($r[1]), $this->rupees($r[2]), $this->rupees($r[3]),
            ])->all();

        return [[$this->col('Month'), $this->col('Collections', 'right'), $this->col('Payments', 'right'), $this->col('Net', 'right')], $rows];
    }

    private function bankWise(?array $range): array
    {
        $rows = Transaction::query()->with(['bank' => fn ($q) => $q->withTrashed()])
            ->when($range, fn ($q) => $q->whereDate('txn_date', '>=', $range[0])->whereDate('txn_date', '<=', $range[1]))
            ->get()
            ->groupBy('bank_id')
            ->map(function ($g) {
                $in = (int) $g->where('direction', 'received')->sum('amount');
                $out = (int) $g->where('direction', 'paid')->sum('amount');

                return [$g->first()->bank?->label() ?? '—', $in, $out, $in - $out];
            })
            ->sortByDesc(fn ($r) => $r[1] + $r[2])->values()
            ->map(fn ($r) => [$r[0], $this->rupees($r[1]), $this->rupees($r[2]), $this->rupees($r[3])])->all();

        return [[$this->col('Bank'), $this->col('Inflow', 'right'), $this->col('Outflow', 'right'), $this->col('Net', 'right')], $rows];
    }

    private function partyWise(?array $range): array
    {
        $rows = Transaction::query()->with(['party' => fn ($q) => $q->withTrashed()])
            ->whereNotNull('party_id')
            ->when($range, fn ($q) => $q->whereDate('txn_date', '>=', $range[0])->whereDate('txn_date', '<=', $range[1]))
            ->get()
            ->groupBy('party_id')
            ->map(function ($g) {
                $recv = (int) $g->where('direction', 'received')->sum('amount');
                $paid = (int) $g->where('direction', 'paid')->sum('amount');

                return [$g->first()->party?->name ?? '—', $recv, $paid, $g->count()];
            })
            ->sortByDesc(fn ($r) => $r[1] + $r[2])->values()
            ->map(fn ($r) => [$r[0], $this->rupees($r[1]), $this->rupees($r[2]), (string) $r[3]])->all();

        return [[$this->col('Party'), $this->col('Received', 'right'), $this->col('Paid', 'right'), $this->col('Entries', 'right')], $rows];
    }

    private function outstanding(): array
    {
        $rows = Party::query()->get()
            ->map(fn (Party $p) => [$p->name, $p->currentBalance()])
            ->filter(fn ($r) => $r[1] !== 0)
            ->sortByDesc(fn ($r) => abs($r[1]))->values()
            ->map(fn ($r) => [$r[0], $this->rupees(abs($r[1])), $r[1] >= 0 ? 'DR — receivable' : 'CR — payable'])->all();

        return [[$this->col('Party'), $this->col('Balance', 'right'), $this->col('Type')], $rows];
    }

    private function ledgerSide(string $side, ?array $range): array
    {
        $rows = LedgerEntry::query()->where($side, '>', 0)
            ->with(['party' => fn ($q) => $q->withTrashed()])
            ->when($range, fn ($q) => $q->whereDate('entry_date', '>=', $range[0])->whereDate('entry_date', '<=', $range[1]))
            ->orderByDesc('entry_date')->orderByDesc('id')->get()
            ->map(fn (LedgerEntry $e) => [
                Dates::human($e->entry_date->format('Y-m-d')),
                $e->party?->name ?? '—',
                $e->particulars,
                $this->rupees($e->{$side}),
            ])->all();

        return [[$this->col('Date'), $this->col('Party'), $this->col('Particulars'), $this->col(ucfirst($side), 'right')], $rows];
    }

    private function ledgerAll(?array $range): array
    {
        $rows = LedgerEntry::query()->with(['party' => fn ($q) => $q->withTrashed()])
            ->when($range, fn ($q) => $q->whereDate('entry_date', '>=', $range[0])->whereDate('entry_date', '<=', $range[1]))
            ->orderBy('entry_date')->orderBy('id')->get()
            ->map(fn (LedgerEntry $e) => [
                Dates::human($e->entry_date->format('Y-m-d')),
                $e->party?->name ?? '—',
                $e->particulars,
                $e->debit > 0 ? $this->rupees($e->debit) : '—',
                $e->credit > 0 ? $this->rupees($e->credit) : '—',
            ])->all();

        return [[$this->col('Date'), $this->col('Party'), $this->col('Particulars'), $this->col('Debit', 'right'), $this->col('Credit', 'right')], $rows];
    }

    // ---- Helpers ----------------------------------------------------------

    /** @return array{0:string,1:string}|null [fromDate, toDate], or null for all-time. */
    private function range(string $period): ?array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay()->toDateString(), $now->copy()->endOfDay()->toDateString()],
            'week' => [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'month' => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'quarter' => [$now->copy()->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()],
            'year' => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            default => null, // 'all'
        };
    }

    private function periodLabel(string $period): string
    {
        return [
            'today' => 'Today',
            'week' => 'This week',
            'month' => 'This month',
            'quarter' => 'This quarter',
            'year' => 'This year',
        ][$period] ?? 'All time';
    }

    /** @return array{label:string, align:string} */
    private function col(string $label, string $align = 'left'): array
    {
        return ['label' => $label, 'align' => $align];
    }

    private function rupees(int $paise): string
    {
        return Inr::format(intdiv($paise, 100));
    }
}
