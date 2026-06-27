<?php

namespace App\DataTables;

use App\Repositories\Contracts\DashboardRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Dashboard widget — most recent money movements across all banks.
 * Has its own ajax route so it never receives the HTML page.
 */
class RecentTxnsDataTable extends BaseDataTable
{
    protected int $defaultPageLength = 8;

    public function __construct(private readonly DashboardRepository $dashboard) {}

    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('party', fn ($row) => $this->avatar($row['party']))
            ->editColumn('type', fn ($row) => $this->statusPill($row['type'], [
                'Received' => 'success',
                'Paid' => 'danger',
            ]))
            ->editColumn('bank', fn ($row) => $this->muted($row['bank']))
            ->editColumn('amount', fn ($row) => $this->signedMoney($row['amount']))
            ->editColumn('status', fn ($row) => $this->statusPill($row['status'], [
                'Cleared' => 'success',
                'Pending' => 'warning',
            ]))
            ->rawColumns(['party', 'type', 'bank', 'amount', 'status'])
            ->setRowId('id');
    }

    public function query(): Collection
    {
        return $this->dashboard->recentTransactions();
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('recent-txns-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('dashboard.recent-txns'))
            ->orderBy(0, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('Txn')->visible(false),
            Column::make('date')->title('Date')->render('window.fmtDate(data)'),
            Column::make('party')->title('Party'),
            Column::make('type')->title('Type'),
            Column::make('bank')->title('Bank'),
            Column::make('status')->title('Status'),
            Column::make('amount')->title('Amount')->addClass('text-right'),
        ];
    }
}
