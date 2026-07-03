<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface PartyRepository
{
    public function all(): Collection;

    /** Soft-deleted rows only (same shape as all()) — for the trash toggle. */
    public function deleted(): Collection;

    public function find(string $name): ?array;

    /** name => name list for dropdowns. */
    public function options(): array;
}
