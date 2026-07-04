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
    public function rows(): Collection
    {
        $party = $this->party();

        if (! $party) {
            return collect();
        }

        $running = $party->opening_balance;
        $entries = $party->ledgerEntries()->orderBy('entry_date')->orderBy('id')->get();

        // Opening-balance row (mirrors the original ledger's first line).
        $openDate = $entries->first()?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $rows = collect([[
            'date' => $openDate,
            'particulars' => 'Opening Balance',
            'vch' => '-',
            'debit' => 0,
            'credit' => 0,
            'balance' => intdiv(abs($running), 100),  // magnitude; side shown via balType
            'balType' => $running >= 0 ? 'DR' : 'CR',
        ]]);

        foreach ($entries as $e) {
            $running += $e->debit - $e->credit;
            $rows->push([
                'date' => $e->entry_date->format('Y-m-d'),
                'particulars' => $e->particulars,
                'vch' => $e->vch ?? '-',
                'debit' => intdiv($e->debit, 100),
                'credit' => intdiv($e->credit, 100),
                'balance' => intdiv(abs($running), 100),  // magnitude; side shown via balType
                'balType' => $running >= 0 ? 'DR' : 'CR',
            ]);
        }

        return $rows;
    }

    public function summary(): LedgerSummary
    {
        $rows = $this->rows();

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

    /** The party whose ledger is shown: first with journal entries, else first party. */
    private function party(): ?Party
    {
        return Party::query()->whereHas('ledgerEntries')->orderBy('id')->first()
            ?? Party::query()->orderBy('name')->first();
    }
}
