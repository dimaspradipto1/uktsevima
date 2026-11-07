<?php

namespace App\DataTables;

use App\Models\TagihanUkt;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TagihanUktDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<TagihanUkt> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('nomor_tagihan', function ($row) {
                return '<span class="fw-bold font-monospace text-primary">' . e($row->nomor_tagihan) . '</span>';
            })
            ->addColumn('mahasiswa', function ($row) {
                if (!$row->mahasiswa) {
                    return '<span class="text-muted">-</span>';
                }
                return '<div class="fw-semibold text-dark">' . e($row->mahasiswa->nama_lengkap) . '</div>' .
                       '<small class="text-muted font-monospace">' . e($row->mahasiswa->nomor_induk_mahasiswa) . '</small>';
            })
            ->addColumn('institusi', function ($row) {
                return $row->institusi
                    ? '<span class="text-dark">' . e($row->institusi->nama_institusi) . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            ->addColumn('periode', function ($row) {
                if (!$row->periodeAkademik) {
                    return '<span class="text-muted">-</span>';
                }
                return '<span class="fw-semibold">' . e($row->periodeAkademik->tahun_akademik) . '</span> ' .
                       '<span class="badge bg-secondary">' . e($row->periodeAkademik->semester) . '</span>';
            })
            ->editColumn('nominal_tagihan', function ($row) {
                return '<span class="fw-bold text-dark">' . $row->format_nominal . '</span>';
            })
            ->addColumn('cicilan_progress', function ($row) {
                $percent = $row->persentase_lunas;
                $barColor = $percent >= 100 ? 'bg-success' : ($percent > 0 ? 'bg-info' : 'bg-secondary');

                return '
                    <div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>' . $row->format_dibayar . '</span>
                            <span class="text-muted">' . $percent . '%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar ' . $barColor . '" role="progressbar" style="width: ' . $percent . '%;"></div>
                        </div>
                        <small class="text-muted d-block mt-1">Sisa: ' . $row->format_sisa . '</small>
                    </div>
                ';
            })
            ->editColumn('tanggal_jatuh_tempo', function ($row) {
                $tgl = $row->tanggal_jatuh_tempo ? $row->tanggal_jatuh_tempo->format('d M Y') : '-';
                if ($row->status !== 'lunas' && $row->tanggal_jatuh_tempo && now()->gt($row->tanggal_jatuh_tempo)) {
                    return '<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>' . $tgl . ' (Lewat)</span>';
                }
                return '<span>' . $tgl . '</span>';
            })
            ->editColumn('status', function ($row) {
                return match($row->status) {
                    'lunas'       => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Lunas</span>',
                    'dicicil'     => '<span class="badge bg-info text-dark"><i class="bi bi-pie-chart me-1"></i>Dicicil</span>',
                    'kadaluarsa'  => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Kadaluarsa</span>',
                    default       => '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Belum Bayar</span>',
                };
            })
            ->addColumn('action', function ($row) {
                $canEdit = $row->status !== 'lunas';
                $canDelete = $row->total_dibayar === 0;

                $editBtn = $canEdit ? '
                    <button type="button" class="btn btn-warning btn-sm btn-edit-tagihan"
                            data-id="' . $row->id . '"
                            data-institusi_id="' . $row->institusi_id . '"
                            data-mahasiswa_id="' . $row->mahasiswa_id . '"
                            data-periode_akademik_id="' . $row->periode_akademik_id . '"
                            data-nominal_tagihan="' . $row->nominal_tagihan . '"
                            data-tanggal_jatuh_tempo="' . ($row->tanggal_jatuh_tempo ? $row->tanggal_jatuh_tempo->format('Y-m-d') : '') . '"
                            title="Edit Tagihan">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                ' : '';

                $deleteBtn = $canDelete ? '
                    <form action="' . route('tagihan-ukts.destroy', $row->id) . '" method="POST" class="d-inline form-delete" data-confirm="Apakah Anda yakin ingin menghapus tagihan UKT ini?">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Tagihan">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                ' : '';

                return '
                    <div class="d-flex justify-content-center gap-1">
                        <a href="' . route('tagihan-ukts.show', $row->id) . '" class="btn btn-info btn-sm text-white" title="Detail & Riwayat Pembayaran">
                            <i class="bi bi-eye"></i>
                        </a>
                        ' . $editBtn . '
                        ' . $deleteBtn . '
                    </div>
                ';
            })
            ->rawColumns(['nomor_tagihan', 'mahasiswa', 'institusi', 'periode', 'nominal_tagihan', 'cicilan_progress', 'tanggal_jatuh_tempo', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<TagihanUkt>
     */
    public function query(TagihanUkt $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['institusi', 'mahasiswa', 'periodeAkademik', 'transaksiPembayarans'])
            ->latest('id');

        // Aturan Multi-Institusi: Jika admin terikat pada satu institusi, batasi datanya
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
                    ->setTableId('tagihan-ukts-table')
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
            Column::make('nomor_tagihan')->title('No. Tagihan'),
            Column::computed('mahasiswa')->title('Mahasiswa'),
            Column::computed('institusi')->title('Institusi Kampus'),
            Column::computed('periode')->title('Periode Semester'),
            Column::make('nominal_tagihan')->title('Nominal UKT'),
            Column::computed('cicilan_progress')->title('Terbayar / Cicilan'),
            Column::make('tanggal_jatuh_tempo')->title('Jatuh Tempo'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                  ->title('Aksi')
                  ->exportable(false)
                  ->printable(false)
                  ->width(120)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'TagihanUkt_' . date('YmdHis');
    }
}
