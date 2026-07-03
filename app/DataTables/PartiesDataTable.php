<?php

namespace App\DataTables;

use App\Repositories\Contracts\PartyRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Party master — customers, vendors, finance companies & agencies.
 */
class PartiesDataTable extends BaseDataTable
{
    public function __construct(private readonly PartyRepository $parties) {}

    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('name', fn ($row) => $this->avatar($row['name']))
            ->editColumn('category', fn ($row) => $this->statusPill($row['category'], [
                'Customer' => 'info',
                'Vendor' => 'accent',
                'Finance Co' => 'indigo',
                'Agency' => 'warning',
            ]))
            ->editColumn('phone', fn ($row) => $this->mono($row['phone']))
            ->editColumn('opening', fn ($row) => $this->money($row['opening']))
            ->editColumn('current', fn ($row) => $this->amount($row['current'], 'plain').' '.$this->drCr($row['balType']))
            ->editColumn('limit', fn ($row) => $this->money($row['limit']))
            ->editColumn('status', fn ($row) => $this->statusPill($row['status'], [
                'Active' => 'success',
                'Inactive' => 'neutral',
            ]))
            ->addColumn('action', fn ($row) => $this->viewingTrash()
                ? $this->trashActions('parties', $row['id'], $row['name'])
                : $this->gatedActions($row['id'], 'parties', route('parties.edit', $row['id']), route('parties.destroy', $row['id']), $row['name']))
            ->rawColumns(['name', 'category', 'phone', 'opening', 'current', 'limit', 'status', 'action'])
            ->setRowId('name');
    }

    public function query(): Collection
    {
        if ($this->viewingTrash()) {
            return $this->parties->deleted();
        }

        $rows = $this->parties->all();
        $category = request('category');

        return $category ? $rows->where('category', $category)->values() : $rows;
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('parties-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.category = document.getElementById("partyCategory")?.value || "";'.$this->trashedAjaxData('parties-table'))
            ->orderBy(0, 'asc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Party'),
            Column::make('category')->title('Category'),
            Column::make('phone')->title('Phone'),
            Column::make('opening')->title('Opening')->addClass('text-right'),
            Column::make('current')->title('Current Balance')->addClass('text-right'),
            Column::make('limit')->title('Credit Limit')->addClass('text-right'),
            Column::make('status')->title('Status'),
            Column::computed('action')->title('')->addClass('text-right')->orderable(false)->searchable(false)->width(80),
        ];
    }
}
