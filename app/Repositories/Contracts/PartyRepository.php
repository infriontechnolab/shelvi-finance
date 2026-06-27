<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface PartyRepository
{
    public function all(): Collection;

    public function find(string $name): ?array;

    /** name => name list for dropdowns. */
    public function options(): array;
}
