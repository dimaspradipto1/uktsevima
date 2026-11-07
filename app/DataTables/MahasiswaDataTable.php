<?php

namespace App\DataTables;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MahasiswaDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Mahasiswa> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('nomor_induk_mahasiswa', function ($row) {
                return '<span class="fw-bold font-monospace text-primary">' . e($row->nomor_induk_mahasiswa) . '</span>';
            })
            ->editColumn('nama_lengkap', function ($row) {
                return '<span class="fw-semibold">' . e($row->nama_lengkap) . '</span>';
            })
            ->addColumn('institusi', function ($row) {
                return $row->institusi
                    ? '<span class="text-dark">' . e($row->institusi->nama_institusi) . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            ->addColumn('user_account', function ($row) {
                if ($row->user) {
                    return '<span class="badge bg-info text-dark" title="' . e($row->user->email) . '"><i class="bi bi-person-check me-1"></i>' . e($row->user->name) . ' (' . e($row->user->email) . ')</span>';
                }
                return '<span class="badge bg-secondary"><i class="bi bi-person-x me-1"></i>Belum Terhubung</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-warning btn-sm btn-edit-mahasiswa"
                                data-id="' . $row->id . '"
                                data-institusi_id="' . $row->institusi_id . '"
                                data-user_id="' . ($row->user_id ?? '') . '"
                                data-nomor_induk_mahasiswa="' . e($row->nomor_induk_mahasiswa) . '"
                                data-nama_lengkap="' . e($row->nama_lengkap) . '"
                                title="Edit Mahasiswa">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form action="' . route('mahasiswas.destroy', $row->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus data mahasiswa ini?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Mahasiswa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['nomor_induk_mahasiswa', 'nama_lengkap', 'institusi', 'user_account', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Mahasiswa>
     */
    public function query(Mahasiswa $model): QueryBuilder
    {
        return $model->newQuery()->with(['institusi', 'user'])->latest('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('mahasiswas-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1, 'desc');
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
            Column::make('nomor_induk_mahasiswa')->title('NIM'),
            Column::make('nama_lengkap')->title('Nama Mahasiswa'),
            Column::computed('institusi')->title('Institusi Kampus'),
            Column::computed('user_account')->title('Akun Login Mahasiswa'),
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
        return 'Mahasiswa_' . date('YmdHis');
    }
}
