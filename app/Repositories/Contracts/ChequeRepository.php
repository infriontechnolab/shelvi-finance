<?php

namespace App\Repositories\Contracts;

use App\Data\ChequeStats;
use Illuminate\Support\Collection;

interface ChequeRepository
{
    public function all(): Collection;

    /** Soft-deleted rows only (same shape as all()) — for the trash toggle. */
    public function deleted(): Collection;

    public function find(string $no): ?array;

    public function stats(): ChequeStats;
}
