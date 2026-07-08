<?php

namespace App\DataTables;

use App\Repositories\Contracts\MoneyRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Money Paid — outbound payments to parties.
 */
class PaidDataTable extends BaseDataTable
{
    public function __construct(private readonly MoneyRepository $money) {}

    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('id', fn ($row) => $this->mono($row['id']))
            ->editColumn('party', fn ($row) => $this->avatar($row['party']))
            ->editColumn('method', fn ($row) => $this->statusPill($row['method'], [
                'Online' => 'info',
                'UPI' => 'accent',
                'Cheque' => 'warning',
                'Cash' => 'success',
            ]))
            ->editColumn('bank', fn ($row) => $this->muted($row['bank']))
            ->editColumn('ref', fn ($row) => $row['ref'] ? $this->mono($row['ref']) : $this->muted('—'))
            ->editColumn('remark', fn ($row) => $this->remark($row['remark']))
            ->editColumn('amount', fn ($row) => $this->amount($row['amount'], 'negative'))
            ->editColumn('status', fn ($row) => $this->statusPill($row['status'], [
                'Cleared' => 'success',
                'Pending' => 'warning',
            ]))
            ->addColumn('action', fn ($row) => $this->viewingTrash()
                ? $this->trashActions('transactions', $row['tid'], $row['id'])
                : $this->gatedActions(
                    $row['id'], 'transactions',
                    route('transactions.edit', $row['tid']), route('transactions.destroy', $row['tid']), $row['id']
                ))
            ->rawColumns(['id', 'party', 'method', 'bank', 'ref', 'remark', 'amount', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(): Collection
    {
        return $this->viewingTrash() ? $this->money->deleted('paid') : $this->money->paid();
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('paid-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', $this->trashedAjaxData('paid-table'))
            ->orderBy(1, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('Voucher'),
            Column::make('date')->title('Date')->render('window.fmtDate(data)'),
            Column::make('party')->title('Party'),
            Column::make('method')->title('Method'),
            Column::make('bank')->title('Bank'),
            Column::make('ref')->title('Reference'),
            Column::make('remark')->title('Remark'),
            Column::make('status')->title('Status'),
            Column::make('amount')->title('Amount')->addClass('text-right'),
            Column::computed('action')->title('')->addClass('text-right')->orderable(false)->searchable(false)->width(80),
        ];
    }
}
