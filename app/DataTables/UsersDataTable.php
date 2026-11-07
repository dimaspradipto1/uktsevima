<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<User> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('role', function ($user) {
                $badgeClass = ($user->role === 'admin') ? 'bg-success' : 'bg-primary';
                return '<span class="badge ' . $badgeClass . '">' . e(ucfirst($user->role ?? '-')) . '</span>';
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at ? $user->created_at->format('d M Y H:i') : '-';
            })
            ->addColumn('action', function ($user) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-info btn-sm text-white btn-change-password" 
                                data-id="' . $user->id . '" 
                                data-name="' . e($user->name) . '" 
                                title="Ubah Password">
                            <i class="bi bi-key"></i>
                        </button>
                        <a href="' . route('users.edit', $user->id) . '" class="btn btn-warning btn-sm" title="Edit Data">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="' . route('users.destroy', $user->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus pengguna ini?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['role', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc');
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::make('name')->title('Nama'),
            Column::make('email')->title('Email'),
            Column::make('role')->title('Hak Akses'),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->width(135)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
