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
use App\Repositories\Eloquent\EloquentReportRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository contract → implementation map. Controllers, DataTables and views
     * depend only on the contracts; the Eloquent implementations are bound here.
     */
    private const REPOSITORIES = [
        PartyRepository::class => EloquentPartyRepository::class,
        BankRepository::class => EloquentBankRepository::class,
        ChequeRepository::class => EloquentChequeRepository::class,
        LedgerRepository::class => EloquentLedgerRepository::class,
        MoneyRepository::class => EloquentMoneyRepository::class,
        DashboardRepository::class => EloquentDashboardRepository::class,
        ReportRepository::class => EloquentReportRepository::class,
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
