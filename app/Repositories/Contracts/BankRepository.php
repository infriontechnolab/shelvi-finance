<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface BankRepository
{
    /** @return array<int, array> */
    public function all(): array;

    /** Soft-deleted banks only (same shape as all()) — for the trash toggle. @return array<int, array> */
    public function deleted(): array;

    public function find(string $id): ?array;

    public function transactions(): Collection;

    /** name => name list for dropdowns. */
    public function options(): array;
}
