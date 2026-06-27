<?php

namespace App\Repositories\Mock;

use App\Data\ChequeStats;
use App\Data\Mock;
use App\Repositories\Contracts\ChequeRepository;
use Illuminate\Support\Collection;

class MockChequeRepository implements ChequeRepository
{
    public function all(): Collection
    {
        return Mock::cheques();
    }

    public function find(string $no): ?array
    {
        return $this->all()->firstWhere('no', $no);
    }

    public function stats(): ChequeStats
    {
        $all = $this->all();

        return new ChequeStats(
            total: $all->count(),
            pending: $all->where('status', 'Pending')->count(),
            cleared: $all->where('status', 'Cleared')->count(),
            bounced: $all->where('status', 'Bounced')->count(),
        );
    }
}
