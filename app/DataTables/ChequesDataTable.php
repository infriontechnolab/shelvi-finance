<?php

namespace App\DataTables;

use App\Repositories\Contracts\ChequeRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Cheque register — issue → deposit → due dates with clearing status.
 */
class ChequesDataTable extends BaseDataTable
{
    public function __construct(private readonly ChequeRepository $cheques) {}

    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('no', fn ($row) => $this->mono('#'.$row['no']))
            ->editColumn('party', fn ($row) => $this->avatar($row['party']))
            ->editColumn('bank', fn ($row) => $this->muted($row['bank']))
            ->editColumn('amount', fn ($row) => $this->money($row['amount']))
            ->editColumn('status', fn ($row) => $this->statusPill($row['status'], [
                'Cleared' => 'success',
                'Pending' => 'warning',
                'Bounced' => 'danger',
            ]))
            ->addColumn('action', fn ($row) => $this->actions($row['no'], editUrl: route('cheques.edit', $row['no'])))
            ->rawColumns(['no', 'party', 'bank', 'amount', 'status', 'action'])
            ->setRowId('no');
    }

    public function query(): Collection
    {
        $rows = $this->cheques->all();
        $status = request('status');

        return $status ? $rows->where('status', $status)->values() : $rows;
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('cheques-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.status = document.getElementById("chequeStatus")?.value || "";')
            ->orderBy(0, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('no')->title('Cheque No'),
            Column::make('party')->title('Party'),
            Column::make('bank')->title('Bank'),
            Column::make('issue')->title('Issue Date')->render('window.fmtDate(data)'),
            Column::make('deposit')->title('Deposit Date')->render('window.fmtDate(data)'),
            Column::make('due')->title('Due Date')->render('window.fmtDate(data)'),
            Column::make('status')->title('Status'),
            Column::make('amount')->title('Amount')->addClass('text-right'),
            Column::computed('action')->title('')->addClass('text-right')->orderable(false)->searchable(false)->width(80),
        ];
    }
}
