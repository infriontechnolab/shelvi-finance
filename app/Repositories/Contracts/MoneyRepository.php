<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface MoneyRepository
{
    public function received(): Collection;

    public function paid(): Collection;

    /** Soft-deleted rows for one direction (same shape) — for the trash toggle. */
    public function deleted(string $direction): Collection;
}
