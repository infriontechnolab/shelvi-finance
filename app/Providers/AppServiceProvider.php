<?php

namespace App\Providers;

use App\Repositories\Contracts\BankRepository;
use App\Repositories\Contracts\ChequeRepository;
use App\Repositories\Contracts\DashboardRepository;
use App\Repositories\Contracts\LedgerRepository;
use App\Repositories\Contracts\MoneyRepository;
use App\Repositories\Contracts\PartyRepository;
use App\Repositories\Contracts\ReportRepository;
use App\Repositories\Eloquent\EloquentBankRepository;
use App\Repositories\Eloquent\EloquentChequeRepository;
use App\Repositories\Eloquent\EloquentDashboardRepository;
use App\Repositories\Eloquent\EloquentLedgerRepository;
use App\Repositories\Eloquent\EloquentMoneyRepository;
use App\Repositories\Eloquent\EloquentPartyRepository;
use App\Repositories\Mock\MockReportRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository contract → implementation map. Data-backed resources use the
     * Eloquent implementations; the report catalogue is a fixed list, so it
     * stays on the Mock (config-style) implementation. Controllers, DataTables
     * and views are agnostic to which implementation is bound.
     */
    private const REPOSITORIES = [
        PartyRepository::class => EloquentPartyRepository::class,
        BankRepository::class => EloquentBankRepository::class,
        ChequeRepository::class => EloquentChequeRepository::class,
        LedgerRepository::class => EloquentLedgerRepository::class,
        MoneyRepository::class => EloquentMoneyRepository::class,
        DashboardRepository::class => EloquentDashboardRepository::class,
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
