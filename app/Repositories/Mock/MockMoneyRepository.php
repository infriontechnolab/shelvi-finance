<?php

namespace App\Repositories\Mock;

use App\Data\Mock;
use App\Repositories\Contracts\MoneyRepository;
use Illuminate\Support\Collection;

class MockMoneyRepository implements MoneyRepository
{
    public function deleted(string $direction): Collection
    {
        return collect(); // mock has no soft-deleted rows
    }

    public function received(): Collection
    {
        return Mock::received();
    }

    public function paid(): Collection
    {
        return Mock::paid();
    }
}
