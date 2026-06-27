<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepository
{
    public function kpis(): array;

    public function weeklyChart(): array;

    public function pendingVerifications(): array;

    public function recentTransactions(): Collection;
}
