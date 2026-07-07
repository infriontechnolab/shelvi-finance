<?php

namespace App\DataTables;

use App\Repositories\Contracts\BankRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Bank account statement — credit/debit lines with running balance.
 */
class BankTxnsDataTable extends BaseDataTable
{
    public function __construct(private readonly BankRepository $banks) {}

    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('desc', fn ($row) => $this->bold($row['desc']))
            ->editColumn('credit', fn ($row) => $row['credit'] > 0 ? $this->amount($row['credit'], 'positive') : $this->muted('—'))
            ->editColumn('debit', fn ($row) => $row['debit'] > 0 ? $this->amount($row['debit'], 'negative') : $this->muted('—'))
            ->editColumn('balance', fn ($row) => $this->money($row['balance']))
            ->rawColumns(['desc', 'credit', 'debit', 'balance'])
            ->setRowId('id');
    }

    public function query(): Collection
    {
        return $this->banks->transactions();
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('bank-txns-table')
            ->columns($this->getColumns())
            ->orderBy(1, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('Ref')->visible(false),
            Column::make('date')->title('Date')->render('window.fmtDate(data)'),
            Column::make('desc')->title('Description'),
            Column::make('credit')->title('Credit')->addClass('text-right'),
            Column::make('debit')->title('Debit')->addClass('text-right'),
            Column::make('balance')->title('Balance')->addClass('text-right'),
        ];
    }
}
