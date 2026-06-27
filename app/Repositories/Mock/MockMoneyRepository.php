<?php

namespace App\Repositories\Mock;

use App\Data\Mock;
use App\Repositories\Contracts\MoneyRepository;
use Illuminate\Support\Collection;

class MockMoneyRepository implements MoneyRepository
{
    public function received(): Collection
    {
        return Mock::received();
    }

    public function paid(): Collection
    {
        return Mock::paid();
    }
}
