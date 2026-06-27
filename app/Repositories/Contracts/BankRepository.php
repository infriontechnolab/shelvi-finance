<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface BankRepository
{
    /** @return array<int, array> */
    public function all(): array;

    public function find(string $id): ?array;

    public function transactions(): Collection;

    /** name => name list for dropdowns. */
    public function options(): array;
}
