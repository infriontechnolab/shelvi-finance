<?php

namespace App\Repositories\Mock;

use App\Data\Mock;
use App\Repositories\Contracts\DashboardRepository;
use Illuminate\Support\Collection;

class MockDashboardRepository implements DashboardRepository
{
    public function kpis(): array
    {
        return Mock::kpis();
    }

    public function weeklyChart(): array
    {
        return Mock::weeklyChart();
    }

    public function pendingVerifications(): array
    {
        return Mock::pendingVerifications();
    }

    public function recentTransactions(): Collection
    {
        return Mock::recentTxns();
    }
}
