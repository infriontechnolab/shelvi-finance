<?php

namespace App\Repositories\Eloquent;

use App\Models\Bank;
use App\Models\Transaction;
use App\Repositories\Contracts\BankRepository;
use Illuminate\Support\Collection;

/**
 * Database-backed bank accounts + statement. Amounts emitted in whole rupees.
 * The statement's running balance is derived from transactions chronologically.
 */
class EloquentBankRepository implements BankRepository
{
    public function all(): array
    {
        return Bank::query()->orderBy('name')->get()->map(fn (Bank $b) => $this->toCard($b))->all();
    }

    public function deleted(): array
    {
        return Bank::onlyTrashed()->orderBy('name')->get()->map(fn (Bank $b) => $this->toCard($b))->all();
    }

    /** @return array<string, mixed> */
    private function toCard(Bank $b): array
    {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'initials' => $b->initials,
            'account' => $b->maskedAccount(),
            'holder' => $b->holder,
            'balance' => intdiv($b->currentBalance(), 100),
            'type' => $b->type,
        ];
    }

    public function find(string $id): ?array
    {
        $bank = Bank::query()->find($id);

        if (! $bank) {
            return null;
        }

        return [
            'id' => $bank->id,
            'name' => $bank->name,
            'initials' => $bank->initials,
            'account' => $bank->account_number,   // full, for editing
            'holder' => $bank->holder,
            'balance' => intdiv($bank->currentBalance(), 100),  // running balance, read-only on the edit form
            'type' => $bank->type,
        ];
    }

    /**
     * Combined statement across all accounts, most-recent first, with a
     * running balance seeded from the sum of opening balances.
     */
    public function transactions(): Collection
    {
        $running = (int) Bank::query()->sum('opening_balance');

        $chron = Transaction::query()
            ->with(['party' => fn ($q) => $q->withTrashed()])
            ->orderBy('txn_date')
            ->orderBy('id')
            ->get()
            ->map(function (Transaction $t) use (&$running) {
                $credit = $t->direction === 'received' ? $t->amount : 0;
                $debit = $t->direction === 'paid' ? $t->amount : 0;
                $running += $credit - $debit;

                return [
                    'id' => $t->reference ?? 'B-'.str_pad((string) $t->id, 4, '0', STR_PAD_LEFT),
                    'date' => $t->txn_date->format('Y-m-d'),
                    'desc' => $t->description ?? $t->party?->name ?? 'Transaction',
                    'credit' => intdiv($credit, 100),
                    'debit' => intdiv($debit, 100),
                    'balance' => intdiv($running, 100),
                ];
            });

        return $chron->reverse()->values();
    }

    public function options(): array
    {
        return Bank::query()->orderBy('name')->get()
            ->mapWithKeys(fn (Bank $b) => [$b->name => "{$b->name} ({$b->account_number})"])
            ->all();
    }
}
