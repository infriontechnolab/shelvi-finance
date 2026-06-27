<?php

namespace App\Repositories\Mock;

use App\Data\Mock;
use App\Repositories\Contracts\BankRepository;
use Illuminate\Support\Collection;

class MockBankRepository implements BankRepository
{
    public function all(): array
    {
        return Mock::banks();
    }

    public function find(string $id): ?array
    {
        return collect($this->all())->firstWhere('id', $id);
    }

    public function transactions(): Collection
    {
        return Mock::bankTxns();
    }

    public function options(): array
    {
        return collect($this->all())->pluck('name', 'name')->all();
    }
}
