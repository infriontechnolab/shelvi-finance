<?php

namespace App\DataTables;

use App\Models\User;
use App\Support\Access;
use Illuminate\Support\Collection;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

/**
 * Application users with their assigned role and active status.
 */
class UsersDataTable extends BaseDataTable
{
    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->editColumn('name', fn ($row) => $this->avatar($row['name']))
            ->editColumn('email', fn ($row) => $this->mono($row['email']))
            ->editColumn('role', fn ($row) => $this->statusPill($row['role'], [
                'Admin' => 'accent',
                'Accountant' => 'info',
                'No role' => 'neutral',
            ]))
            ->editColumn('status', fn ($row) => $this->statusPill($row['status'], [
                'Active' => 'success',
                'Inactive' => 'neutral',
            ]))
            ->addColumn('action', fn ($row) => $this->gatedActions(
                $row['id'], 'users', route('users.edit', $row['id']), route('users.destroy', $row['id']), $row['name']
            ))
            ->rawColumns(['name', 'email', 'role', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(): Collection
    {
        return User::query()->with('roles')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', Access::hiddenRoles()))
            ->orderBy('name')->get()->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->roles->isNotEmpty() ? ucfirst($u->roles->first()->name) : 'No role',
                'status' => $u->is_active ? 'Active' : 'Inactive',
            ]);
    }

    public function html(): HtmlBuilder
    {
        return $this->baseBuilder('users-table')
            ->columns($this->getColumns())
            ->orderBy(0, 'asc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Name'),
            Column::make('email')->title('Email'),
            Column::make('role')->title('Role'),
            Column::make('status')->title('Status'),
            Column::computed('action')->title('')->addClass('text-right')->orderable(false)->searchable(false)->width(80),
        ];
    }
}
