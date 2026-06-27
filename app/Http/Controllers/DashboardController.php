<?php

namespace App\Http\Controllers;

use App\DataTables\RecentTxnsDataTable;
use App\Repositories\Contracts\DashboardRepository;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardRepository $dashboard) {}

    public function index(RecentTxnsDataTable $txns)
    {
        return view('pages.dashboard', [
            'txnsTable' => $txns->html(),
            'kpis' => $this->dashboard->kpis(),
            'weeklyChart' => $this->dashboard->weeklyChart(),
            'pending' => $this->dashboard->pendingVerifications(),
        ]);
    }

    /** Dedicated ajax route for the recent-transactions widget. */
    public function recentTxns(RecentTxnsDataTable $dataTable)
    {
        return $dataTable->ajax();
    }
}
