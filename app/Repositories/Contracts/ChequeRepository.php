<?php

namespace App\Repositories\Contracts;

use App\Data\ChequeStats;
use Illuminate\Support\Collection;

interface ChequeRepository
{
    public function all(): Collection;

    public function find(string $no): ?array;

    public function stats(): ChequeStats;
}
