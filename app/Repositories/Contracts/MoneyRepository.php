<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface MoneyRepository
{
    public function received(): Collection;

    public function paid(): Collection;
}
