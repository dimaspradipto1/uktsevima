<?php

namespace App\DataTables;

use App\Models\TransaksiPembayaran;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TransaksiPembayaranDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<TransaksiPembayaran> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('gateway_transaction_id', function ($row) {
                return '<span class="fw-bold font-monospace text-primary">' . e($row->gateway_transaction_id ?? '-') . '</span>';
            })
            ->addColumn('tagihan', function ($row) {
                if (!$row->tagihanUkt) {
                    return '<span class="text-muted">-</span>';
                }
                return '<span class="fw-semibold font-monospace text-dark">' . e($row->tagihanUkt->nomor_tagihan) . '</span>';
            })
            ->addColumn('mahasiswa', function ($row) {
                $mhs = $row->tagihanUkt?->mahasiswa;
                if (!$mhs) {
                    return '<span class="text-muted">-</span>';
                }
                return '<div class="fw-semibold text-dark">' . e($mhs->nama_lengkap) . '</div>' .
                       '<small class="text-muted font-monospace">' . e($mhs->nomor_induk_mahasiswa) . '</small>';
            })
            ->addColumn('institusi', function ($row) {
                return $row->institusi
                    ? '<span class="text-dark">' . e($row->institusi->nama_institusi) . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            ->editColumn('nominal_rupiah', function ($row) {
                return '<span class="fw-bold text-success">' . $row->format_nominal . '</span>';
            })
            ->editColumn('metode_pembayaran', function ($row) {
                $badge = match(strtoupper($row->metode_pembayaran ?? '')) {
                    'QRIS'     => 'bg-danger',
                    'VA'       => 'bg-primary',
                    'TUNAI'    => 'bg-success',
                    'TRANSFER' => 'bg-info text-dark',
                    default    => 'bg-secondary',
                };
                return '<span class="badge ' . $badge . '">' . e($row->metode_pembayaran ?? '-') . '</span>';
            })
            ->editColumn('status', function ($row) {
                return match($row->status) {
                    'berhasil'     => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Berhasil</span>',
                    'menunggu'     => '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>',
                    'gagal'        => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Gagal</span>',
                    'kedaluwarsa'  => '<span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i>Kedaluwarsa</span>',
                    default        => '<span class="badge bg-light text-dark">' . e($row->status) . '</span>',
                };
            })
            ->editColumn('dibayar_pada', function ($row) {
                return $row->dibayar_pada ? $row->dibayar_pada->format('d M Y H:i') : '<span class="text-muted">-</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-warning btn-sm btn-edit-transaksi"
                                data-id="' . $row->id . '"
                                data-institusi_id="' . $row->institusi_id . '"
                                data-tagihan_ukt_id="' . $row->tagihan_ukt_id . '"
                                data-gateway_transaction_id="' . e($row->gateway_transaction_id ?? '') . '"
                                data-nominal_rupiah="' . $row->nominal_rupiah . '"
                                data-metode_pembayaran="' . e($row->metode_pembayaran ?? '') . '"
                                data-status="' . e($row->status) . '"
                                data-dibayar_pada="' . ($row->dibayar_pada ? $row->dibayar_pada->format('Y-m-d\TH:i') : '') . '"
                                title="Edit Transaksi">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form action="' . route('transaksi-pembayarans.destroy', $row->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus data transaksi ini?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Transaksi">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['gateway_transaction_id', 'tagihan', 'mahasiswa', 'institusi', 'nominal_rupiah', 'metode_pembayaran', 'status', 'dibayar_pada', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<TransaksiPembayaran>
     */
    public function query(TransaksiPembayaran $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['institusi', 'tagihanUkt.mahasiswa'])->latest('id');

        $user = auth()->user();

        // 1. Jika mahasiswa, batasi hanya transaksi miliknya
        if ($user && $user->role === 'mahasiswa' && $user->mahasiswa) {
            $query->whereHas('tagihanUkt', function ($q) use ($user) {
                $q->where('mahasiswa_id', $user->mahasiswa->id);
            });
        }
        // 2. Jika admin institusi, batasi hanya transaksi kampusnya
        elseif ($user && $user->institusi_id) {
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
                    ->setTableId('transaksi-pembayarans-table')
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
            Column::make('gateway_transaction_id')->title('ID Transaksi'),
            Column::computed('tagihan')->title('No. Tagihan UKT'),
            Column::computed('mahasiswa')->title('Mahasiswa'),
            Column::computed('institusi')->title('Institusi Kampus'),
            Column::make('nominal_rupiah')->title('Nominal'),
            Column::make('metode_pembayaran')->title('Metode'),
            Column::make('status')->title('Status'),
            Column::make('dibayar_pada')->title('Waktu Bayar'),
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
        return 'TransaksiPembayaran_' . date('YmdHis');
    }
}
