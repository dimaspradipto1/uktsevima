<?php

namespace App\DataTables;

use App\Models\PeriodeAkademik;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PeriodeAkademikDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<PeriodeAkademik> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('institusi', function ($row) {
                return $row->institusi
                    ? '<span class="text-dark">' . e($row->institusi->nama_institusi) . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            ->editColumn('tahun_akademik', function ($row) {
                return '<span class="fw-bold font-monospace">' . e($row->tahun_akademik) . '</span>';
            })
            ->editColumn('semester', function ($row) {
                $badge = match(strtolower($row->semester)) {
                    'ganjil' => 'bg-primary',
                    'genap'  => 'bg-success',
                    default  => 'bg-warning text-dark',
                };
                return '<span class="badge ' . $badge . '">' . e(ucfirst($row->semester)) . '</span>';
            })
            ->editColumn('tanggal_mulai', function ($row) {
                return $row->tanggal_mulai ? $row->tanggal_mulai->format('d M Y') : '-';
            })
            ->editColumn('tanggal_selesai', function ($row) {
                return $row->tanggal_selesai ? $row->tanggal_selesai->format('d M Y') : '-';
            })
            ->addColumn('status_periode', function ($row) {
                $now = now()->startOfDay();
                if ($row->tanggal_mulai && $row->tanggal_selesai) {
                    if ($now->between($row->tanggal_mulai, $row->tanggal_selesai)) {
                        return '<span class="badge bg-success"><i class="bi bi-clock-history me-1"></i>Berjalan</span>';
                    } elseif ($now->lt($row->tanggal_mulai)) {
                        return '<span class="badge bg-info text-dark"><i class="bi bi-hourglass-split me-1"></i>Mendatang</span>';
                    } else {
                        return '<span class="badge bg-secondary"><i class="bi bi-check2-all me-1"></i>Selesai</span>';
                    }
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-warning btn-sm btn-edit-periode"
                                data-id="' . $row->id . '"
                                data-institusi_id="' . $row->institusi_id . '"
                                data-tahun_akademik="' . e($row->tahun_akademik) . '"
                                data-semester="' . e($row->semester) . '"
                                data-tanggal_mulai="' . ($row->tanggal_mulai ? $row->tanggal_mulai->format('Y-m-d') : '') . '"
                                data-tanggal_selesai="' . ($row->tanggal_selesai ? $row->tanggal_selesai->format('Y-m-d') : '') . '"
                                title="Edit Periode">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form action="' . route('periode-akademiks.destroy', $row->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus periode akademik ini?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['institusi', 'tahun_akademik', 'semester', 'status_periode', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<PeriodeAkademik>
     */
    public function query(PeriodeAkademik $model): QueryBuilder
    {
        $query = $model->newQuery()->with('institusi')->latest('id');

        $user = auth()->user();
        if ($user && $user->institusi_id) {
            $query->where('institusi_id', $user->institusi_id);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('periode-akademiks-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(2, 'desc');
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
            Column::computed('institusi')->title('Institusi Kampus'),
            Column::make('tahun_akademik')->title('Tahun Akademik'),
            Column::make('semester')->title('Semester'),
            Column::make('tanggal_mulai')->title('Mulai'),
            Column::make('tanggal_selesai')->title('Selesai'),
            Column::computed('status_periode')->title('Status'),
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
        return 'PeriodeAkademik_' . date('YmdHis');
    }
}
