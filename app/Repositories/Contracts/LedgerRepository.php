<?php

namespace App\Repositories\Contracts;

use App\Data\LedgerSummary;
use Illuminate\Support\Collection;

interface LedgerRepository
{
    public function rows(): Collection;

    public function summary(): LedgerSummary;

    public function defaultParty(): string;
}
