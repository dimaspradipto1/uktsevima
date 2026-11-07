<?php

namespace App\DataTables;

use App\Models\LogWebhook;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LogWebhookDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<LogWebhook> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('diterima_pada', function ($row) {
                $tgl = $row->diterima_pada ?? $row->created_at;
                return $tgl ? $tgl->format('d M Y H:i:s') : '-';
            })
            ->editColumn('gateway_event_id', function ($row) {
                return '<span class="fw-bold font-monospace text-primary">' . e($row->gateway_event_id ?? '-') . '</span>';
            })
            ->editColumn('jenis_event', function ($row) {
                $badge = match($row->jenis_event) {
                    'payment.success' => 'bg-success',
                    'payment.expired' => 'bg-warning text-dark',
                    'payment.failed'  => 'bg-danger',
                    default           => 'bg-info text-dark',
                };
                return '<span class="badge ' . $badge . '">' . e($row->jenis_event ?? '-') . '</span>';
            })
            ->addColumn('institusi', function ($row) {
                if ($row->institusi) {
                    return '<span class="text-dark">' . e($row->institusi->nama_institusi) . '</span>';
                } elseif ($row->kode_institusi_diterima) {
                    return '<span class="text-muted">' . e($row->kode_institusi_diterima) . ' (Tidak Terdaftar)</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->editColumn('signature_valid', function ($row) {
                return $row->signature_valid
                    ? '<span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Valid (HMAC)</span>'
                    : '<span class="badge bg-danger"><i class="bi bi-shield-x me-1"></i>Invalid / Ditolak</span>';
            })
            ->editColumn('status_pemprosesan', function ($row) {
                return match($row->status_pemprosesan) {
                    'diproses' => '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Diproses</span>',
                    'diterima' => '<span class="badge bg-primary"><i class="bi bi-inbox me-1"></i>Diterima</span>',
                    'duplikat' => '<span class="badge bg-warning text-dark"><i class="bi bi-files me-1"></i>Duplikat</span>',
                    'ditolak'  => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>',
                    'gagal'    => '<span class="badge bg-dark"><i class="bi bi-exclamation-octagon me-1"></i>Gagal</span>',
                    default    => '<span class="badge bg-secondary">' . e($row->status_pemprosesan) . '</span>',
                };
            })
            ->editColumn('kode_respons_http', function ($row) {
                $code = $row->kode_respons_http;
                if (!$code) return '<span class="text-muted">-</span>';

                $color = match(true) {
                    $code >= 200 && $code < 300 => 'bg-success',
                    $code >= 400 && $code < 500 => 'bg-warning text-dark',
                    default                      => 'bg-danger',
                };
                return '<span class="badge ' . $color . '">' . $code . '</span>';
            })
            ->editColumn('alamat_ip', function ($row) {
                return '<span class="font-monospace small text-muted">' . e($row->alamat_ip ?? '-') . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-info btn-sm text-white btn-view-payload"
                                data-id="' . $row->id . '"
                                data-gateway_event_id="' . e($row->gateway_event_id ?? '') . '"
                                data-jenis_event="' . e($row->jenis_event ?? '') . '"
                                data-kode_institusi="' . e($row->kode_institusi_diterima ?? '') . '"
                                data-signature_diterima="' . e($row->signature_diterima ?? '') . '"
                                data-signature_valid="' . ($row->signature_valid ? '1' : '0') . '"
                                data-status_pemprosesan="' . e($row->status_pemprosesan) . '"
                                data-kode_respons_http="' . ($row->kode_respons_http ?? '') . '"
                                data-pesan_kesalahan="' . e($row->pesan_kesalahan ?? '') . '"
                                data-payload_json="' . htmlspecialchars(json_encode($row->payload_json, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '"
                                title="Lihat Payload & Detail">
                            <i class="bi bi-code-slash"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-edit-log"
                                data-id="' . $row->id . '"
                                data-status_pemprosesan="' . e($row->status_pemprosesan) . '"
                                data-kode_respons_http="' . ($row->kode_respons_http ?? '200') . '"
                                data-pesan_kesalahan="' . e($row->pesan_kesalahan ?? '') . '"
                                title="Edit Status Log">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <form action="' . route('log-webhooks.destroy', $row->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus catatan log webhook ini?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Log">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['diterima_pada', 'gateway_event_id', 'jenis_event', 'institusi', 'signature_valid', 'status_pemprosesan', 'kode_respons_http', 'alamat_ip', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<LogWebhook>
     */
    public function query(LogWebhook $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['institusi', 'transaksiPembayaran'])->latest('id');

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
                    ->setTableId('log-webhooks-table')
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
            Column::computed('DT_RowIndex')
                  ->title('No.')
                  ->width(40)
                  ->searchable(false)
                  ->orderable(false)
                  ->addClass('text-center'),
            Column::make('diterima_pada')->title('Waktu Masuk'),
            Column::make('gateway_event_id')->title('Event ID Gateway'),
            Column::make('jenis_event')->title('Jenis Event'),
            Column::computed('institusi')->title('Institusi Kampus'),
            Column::computed('signature_valid')->title('Verifikasi HMAC'),
            Column::make('status_pemprosesan')->title('Status Pemrosesan'),
            Column::make('kode_respons_http')->title('HTTP Code')->addClass('text-center'),
            Column::make('alamat_ip')->title('IP Pengirim'),
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
        return 'LogWebhook_' . date('YmdHis');
    }
}
