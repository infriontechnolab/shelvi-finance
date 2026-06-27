<?php

namespace App\Repositories\Mock;

use App\Data\Mock;
use App\Repositories\Contracts\PartyRepository;
use Illuminate\Support\Collection;

class MockPartyRepository implements PartyRepository
{
    public function all(): Collection
    {
        return Mock::parties();
    }

    public function find(string $name): ?array
    {
        return $this->all()->firstWhere('name', $name);
    }

    public function options(): array
    {
        return $this->all()->pluck('name', 'name')->all();
    }
}
