<?php

namespace App\Repositories\Eloquent;

use App\Data\ChequeStats;
use App\Models\Cheque;
use App\Repositories\Contracts\ChequeRepository;
use Illuminate\Support\Collection;

/**
 * Database-backed cheques. Amounts in whole rupees; dates as ISO strings.
 */
class EloquentChequeRepository implements ChequeRepository
{
    public function all(): Collection
    {
        return Cheque::query()->with(['party' => fn ($q) => $q->withTrashed(), 'bank' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('issue_date')
            ->get()
            ->map(fn (Cheque $c) => $this->toRow($c));
    }

    public function deleted(): Collection
    {
        return Cheque::onlyTrashed()->with(['party' => fn ($q) => $q->withTrashed(), 'bank' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('issue_date')
            ->get()
            ->map(fn (Cheque $c) => $this->toRow($c));
    }

    public function find(string $id): ?array
    {
        $cheque = Cheque::query()->with(['party' => fn ($q) => $q->withTrashed(), 'bank' => fn ($q) => $q->withTrashed()])->find($id);

        return $cheque ? $this->toRow($cheque) : null;
    }

    private function toRow(Cheque $c): array
    {
        return [
            'id' => $c->id,
            'no' => $c->cheque_no,
            'party' => $c->party?->name,
            'bank' => $c->bank?->name,
            'amount' => intdiv($c->amount, 100),
            'issue' => $c->issue_date?->format('Y-m-d'),
            'deposit' => $c->deposit_date?->format('Y-m-d'),
            'due' => $c->due_date?->format('Y-m-d'),
            'status' => $c->status,
        ];
    }

    public function stats(): ChequeStats
    {
        $counts = Cheque::query()
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return new ChequeStats(
            total: (int) $counts->sum(),
            pending: (int) ($counts['Pending'] ?? 0),
            cleared: (int) ($counts['Cleared'] ?? 0),
            bounced: (int) ($counts['Bounced'] ?? 0),
        );
    }
}
