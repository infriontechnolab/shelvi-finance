<?php

namespace App\Providers;

use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\ChequeRepository;
use App\Repositories\Contracts\DashboardRepository;
use App\Repositories\Contracts\LedgerRepository;
use App\Repositories\Contracts\MoneyRepository;
use App\Repositories\Contracts\PartyRepository;
use App\Repositories\Contracts\ReportRepository;
use App\Repositories\Mock\MockBankRepository;
use App\Repositories\Mock\MockChequeRepository;
use App\Repositories\Mock\MockDashboardRepository;
use App\Repositories\Mock\MockLedgerRepository;
use App\Repositories\Mock\MockMoneyRepository;
use App\Repositories\Mock\MockPartyRepository;
use App\Repositories\Mock\MockReportRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository contract → implementation map. Swap the Mock* values for
     * Eloquent* implementations when a real database lands — nothing else
     * (controllers, DataTables, views) needs to change.
     */
    private const REPOSITORIES = [
        PartyRepository::class => MockPartyRepository::class,
        BankRepository::class => MockBankRepository::class,
        ChequeRepository::class => MockChequeRepository::class,
        LedgerRepository::class => MockLedgerRepository::class,
        MoneyRepository::class => MockMoneyRepository::class,
        DashboardRepository::class => MockDashboardRepository::class,
        ReportRepository::class => MockReportRepository::class,
    ];

    public function register(): void
    {
        foreach (self::REPOSITORIES as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }

    public function boot(): void
    {
        //
    }
}
