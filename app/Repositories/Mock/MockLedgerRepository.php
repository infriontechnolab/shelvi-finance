<?php

namespace App\Repositories\Mock;

use App\Data\LedgerSummary;
use App\Data\Mock;
use App\Repositories\Contracts\LedgerRepository;
use Illuminate\Support\Collection;

class MockLedgerRepository implements LedgerRepository
{
    public function rows(): Collection
    {
        return Mock::ledger();
    }

    public function summary(): LedgerSummary
    {
        $rows = $this->rows();
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
        return Mock::ledgerParty();
    }
}
