<?php

namespace App\Repositories\Eloquent;

use App\Data\LedgerSummary;
use App\Models\Party;
use App\Repositories\Contracts\LedgerRepository;
use Illuminate\Support\Collection;

/**
 * Database-backed party ledger for the default party. Running balance and
 * DR/CR are derived by walking the journal chronologically. Rupees throughout.
 */
class EloquentLedgerRepository implements LedgerRepository
{
    public function rows(?string $party = null): Collection
    {
        $p = $this->party($party);

        if (! $p) {
            return collect();
        }

        $running = $p->opening_balance;
        $entries = $p->ledgerEntries()->with(['transaction' => fn ($q) => $q->with('bank')])
            ->orderBy('entry_date')->orderBy('id')->get();

        // Opening-balance row (mirrors the original ledger's first line).
        $openDate = $entries->first()?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $rows = collect([[
            'date' => $openDate,
            'particulars' => 'Opening Balance',
            'customer' => '-',
            'method' => '-',
            'bank' => '-',
            'vch' => '-',
            'payeeHolder' => '-',
            'payeeAccount' => '-',
            'remark' => '-',
            'debit' => 0,
            'credit' => 0,
            'balance' => intdiv(abs($running), 100),  // magnitude; side shown via balType
            'balType' => $running >= 0 ? 'DR' : 'CR',
        ]]);

        foreach ($entries as $e) {
            $running += $e->debit - $e->credit;
            $t = $e->transaction;
            $rows->push([
                'date' => $e->entry_date->format('Y-m-d'),
                'particulars' => $e->particulars,
                'customer' => $t?->customer_name ?: '-',
                'method' => $t?->method ?: '-',
                'bank' => $t?->bank?->label() ?: '-',
                'vch' => $e->vch ?? '-',
                'payeeHolder' => $t?->payee_holder ?: '-',
                'payeeAccount' => $t?->payee_account_no ?: '-',
                'remark' => $t?->remark ?: '-',
                'debit' => intdiv($e->debit, 100),
                'credit' => intdiv($e->credit, 100),
                'balance' => intdiv(abs($running), 100),  // magnitude; side shown via balType
                'balType' => $running >= 0 ? 'DR' : 'CR',
            ]);
        }

        return $rows;
    }

    public function summary(?string $party = null): LedgerSummary
    {
        $rows = $this->rows($party);

        if ($rows->isEmpty()) {
            $today = now()->format('Y-m-d');

            return new LedgerSummary(
                opening: 0,
                totalDebit: 0,
                totalCredit: 0,
                closing: 0,
                closingType: 'DR',
                from: $today,
                to: $today,
            );
        }

        $closing = $rows->last();

        return new LedgerSummary(
            opening: $rows->first()['balance'],
            totalDebit: $rows->sum('debit'),
            totalCredit: $rows->sum('credit'),
            closing: $closing['balance'],
            closingType: $closing['balType'],
            from: $rows->first()['date'],
            to: $closing['date'],
        );
    }

    public function defaultParty(): string
    {
        return $this->party()?->name ?? '';
    }

    /**
     * The party whose ledger is shown: the requested one by name if found,
     * else the first with journal entries, else the first party.
     */
    private function party(?string $name = null): ?Party
    {
        if ($name && $found = Party::query()->where('name', $name)->first()) {
            return $found;
        }

        return Party::query()->whereHas('ledgerEntries')->orderBy('id')->first()
            ?? Party::query()->orderBy('name')->first();
    }
}
