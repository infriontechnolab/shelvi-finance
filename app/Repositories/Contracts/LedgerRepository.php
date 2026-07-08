<?php

namespace App\Repositories\Contracts;

use App\Data\LedgerSummary;
use Illuminate\Support\Collection;

interface LedgerRepository
{
    public function rows(?string $party = null): Collection;

    public function summary(?string $party = null): LedgerSummary;

    public function defaultParty(): string;
}
