<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use App\Repositories\Contracts\MoneyRepository;
use Illuminate\Support\Collection;

/**
 * Database-backed money movements. Received/paid are the two directions of the
 * unified transactions table. Amounts emitted in whole rupees, positive.
 */
class EloquentMoneyRepository implements MoneyRepository
{
    public function received(): Collection
    {
        return $this->rows('received', 'R');
    }

    public function paid(): Collection
    {
        return $this->rows('paid', 'P');
    }

    public function deleted(string $direction): Collection
    {
        return $this->rows($direction, $direction === 'received' ? 'R' : 'P', trashed: true);
    }

    private function rows(string $direction, string $prefix, bool $trashed = false): Collection
    {
        return Transaction::query()->when($trashed, fn ($q) => $q->onlyTrashed())
            ->where('direction', $direction)
            ->with(['party' => fn ($q) => $q->withTrashed(), 'bank' => fn ($q) => $q->withTrashed()])
            ->orderByDesc('txn_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Transaction $t) => [
                'id' => $prefix.'-'.str_pad((string) $t->id, 4, '0', STR_PAD_LEFT),
                'tid' => $t->id,
                'date' => $t->txn_date->format('Y-m-d'),
                'party' => $t->party?->name ?? '—',
                'method' => $t->method,
                'bank' => $t->bank?->name,
                'ref' => $t->reference,
                'amount' => intdiv($t->amount, 100),
                'status' => $t->status,
            ]);
    }
}
