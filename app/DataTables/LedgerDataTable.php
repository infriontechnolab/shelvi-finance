<?php

namespace App\DataTables;

use App\Repositories\Contracts\LedgerRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Party Ledger — chronological debit/credit with running balance.
 * Default order preserves chronological flow (running balance only makes
 * sense in date order), so the table is rendered unsorted by default.
 */
class LedgerDataTable extends BaseDataTable
{
    protected int $defaultPageLength = 25;

    public function __construct(private readonly LedgerRepository $ledger) {}

    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('particulars', fn ($row) => $this->bold($row['particulars']))
            ->editColumn('customer', fn ($row) => $row['customer'] === '-' ? $this->muted('—') : $this->mono($row['customer']))
            ->editColumn('vch', fn ($row) => $row['vch'] === '-' ? $this->muted('—') : $this->mono($row['vch']))
            ->editColumn('remark', fn ($row) => $this->remark($row['remark'] === '-' ? null : $row['remark']))
            ->editColumn('debit', fn ($row) => $row['debit'] > 0 ? $this->amount($row['debit'], 'negative') : $this->muted('—'))
            ->editColumn('credit', fn ($row) => $row['credit'] > 0 ? $this->amount($row['credit'], 'positive') : $this->muted('—'))
            ->editColumn('balance', fn ($row) => $this->amount($row['balance'], $row['balType'] === 'DR' ? 'negative' : 'positive').' '.$this->drCr($row['balType'], 'entry'))
            ->rawColumns(['particulars', 'customer', 'vch', 'remark', 'debit', 'credit', 'balance']);
    }

    public function query(): Collection
    {
        return $this->ledger->rows(request('party'));
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('ledger-table')
            ->columns($this->getColumns())
            ->orderBy(0, 'asc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('date')->title('Date')->render('window.fmtDate(data)'),
            Column::make('particulars')->title('Particulars'),
            Column::make('customer')->title('Customer Name'),
            Column::make('vch')->title('Voucher'),
            Column::make('remark')->title('Remark'),
            Column::make('debit')->title('Debit')->addClass('text-right'),
            Column::make('credit')->title('Credit')->addClass('text-right'),
            Column::make('balance')->title('Balance')->addClass('text-right'),
        ];
    }
}
