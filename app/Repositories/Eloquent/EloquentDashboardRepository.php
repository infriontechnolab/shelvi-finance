<?php

namespace App\Repositories\Eloquent;

use App\Models\Bank;
use App\Models\Cheque;
use App\Models\Party;
use App\Models\Transaction;
use App\Repositories\Contracts\DashboardRepository;
use Illuminate\Support\Collection;

/**
 * Database-backed dashboard aggregates. All monetary values in whole rupees.
 * KPIs reflect totals over the dataset (not a fixed "today"), computed live.
 */
class EloquentDashboardRepository implements DashboardRepository
{
    public function kpis(): array
    {
        $bankBalance = Bank::query()->get()->sum(fn (Bank $b) => $b->currentBalance());
        $collections = (int) Transaction::query()->where('direction', 'received')->sum('amount');
        $payments = (int) Transaction::query()->where('direction', 'paid')->sum('amount');

        $toReceive = 0;
        $toPay = 0;
        foreach (Party::query()->get() as $p) {
            $bal = $p->currentBalance();
            $bal >= 0 ? $toReceive += $bal : $toPay += $bal;
        }

        $pendingCheques = Cheque::query()->where('status', 'Pending')->count();

        return [
            $this->kpi('Total Bank Balance', intdiv($bankBalance, 100), 'navy'),
            $this->kpi('Total Collections', intdiv($collections, 100), 'success'),
            $this->kpi('Total Payments', -intdiv($payments, 100), 'danger'),
            $this->kpi('Amount to Receive', intdiv($toReceive, 100), 'teal'),
            $this->kpi('Amount to Pay', intdiv($toPay, 100), 'gold'),
            $this->kpi('Pending Cheques', $pendingCheques, 'gold', isCount: true),
        ];
    }

    public function weeklyChart(): array
    {
        $buckets = [1 => ['collection' => 0, 'payment' => 0], 2 => ['collection' => 0, 'payment' => 0],
            3 => ['collection' => 0, 'payment' => 0], 4 => ['collection' => 0, 'payment' => 0]];

        foreach (Transaction::query()->get() as $t) {
            $week = min(4, (int) ceil($t->txn_date->day / 7));
            $key = $t->direction === 'received' ? 'collection' : 'payment';
            $buckets[$week][$key] += $t->amount;
        }

        return collect($buckets)->map(fn ($b, $w) => [
            'week' => 'W'.$w,
            'collection' => intdiv($b['collection'], 100),
            'payment' => intdiv($b['payment'], 100),
        ])->values()->all();
    }

    public function pendingVerifications(): array
    {
        return Cheque::query()->with(['party' => fn ($q) => $q->withTrashed()])->where('status', 'Pending')
            ->orderByDesc('due_date')
            ->limit(4)
            ->get()
            ->map(fn (Cheque $c) => [
                'party' => $c->party?->name ?? '—',
                'amount' => intdiv($c->amount, 100),
                'date' => $c->due_date->format('Y-m-d'),
            ])->all();
    }

    public function recentTransactions(): Collection
    {
        return Transaction::query()->with(['party' => fn ($q) => $q->withTrashed(), 'bank' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('txn_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn (Transaction $t) => [
                'id' => 'T-'.str_pad((string) $t->id, 4, '0', STR_PAD_LEFT),
                'date' => $t->txn_date->format('Y-m-d'),
                'party' => $t->party?->name ?? '—',
                'type' => ucfirst($t->direction),
                'bank' => $t->bank?->name,
                'amount' => $t->direction === 'paid' ? -intdiv($t->amount, 100) : intdiv($t->amount, 100),
                'status' => $t->status,
            ]);
    }

    private function kpi(string $label, int $value, string $tone, bool $isCount = false): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'tone' => $tone,
            'trend' => '',
            'up' => $value >= 0,
            'isCount' => $isCount,
        ];
    }
}
