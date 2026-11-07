<?php

namespace App\DataTables;

use App\Models\Institusi;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InstitusiDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Institusi> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('kode_institusi', function ($row) {
                return '<span class="badge bg-light text-dark border font-monospace px-2 py-1">' . e($row->kode_institusi) . '</span>';
            })
            ->editColumn('secret_key', function ($row) {
                return '<code class="small text-truncate d-inline-block" style="max-width: 150px;" title="' . e($row->secret_key) . '">' . e(substr($row->secret_key, 0, 16)) . '...</code>';
            })
            ->editColumn('status', function ($row) {
                return $row->status
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
                    : '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <a href="' . route('institusis.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit Institusi">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <form action="' . route('institusis.destroy', $row->id) . '" method="POST" class="d-inline form-delete" data-confirm="Apakah Anda yakin ingin menghapus institusi ini?">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['kode_institusi', 'secret_key', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Institusi>
     */
    public function query(Institusi $model): QueryBuilder
    {
        return $model->newQuery()->latest('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('institusis-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1, 'asc');
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')
                  ->title('No.')
                  ->width(40)
                  ->searchable(false)
                  ->orderable(false)
                  ->addClass('text-center'),
            Column::make('kode_institusi')->title('Kode Institusi'),
            Column::make('nama_institusi')->title('Nama Institusi'),
            Column::make('secret_key')->title('Secret Key (HMAC)'),
            Column::make('status')->title('Status'),
            Column::make('created_at')->title('Dibuat Pada'),
            Column::computed('action')
                  ->title('Aksi')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Institusi_' . date('YmdHis');
    }
}
