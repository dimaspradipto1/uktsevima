<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogWebhook extends Model
{
    use HasFactory;

    protected $table = 'log_webhooks';

    protected $fillable = [
        'institusi_id',
        'transaksi_pembayaran_id',
        'kode_institusi_diterima',
        'gateway_event_id',
        'jenis_event',
        'signature_diterima',
        'signature_valid',
        'payload_asli',
        'payload_json',
        'status_pemprosesan', // diterima, diproses, ditolak, gagal, duplikat
        'kode_respons_http',
        'alamat_ip',
        'pesan_kesalahan',
        'diterima_pada',
        'diproses_pada',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid'   => 'boolean',
            'payload_json'      => 'array',
            'kode_respons_http' => 'integer',
            'diterima_pada'     => 'datetime',
            'diproses_pada'     => 'datetime',
        ];
    }

    /**
     * Relasi ke Institusi Kampus
     */
    public function institusi(): BelongsTo
    {
        return $this->belongsTo(Institusi::class, 'institusi_id');
    }

    /**
     * Relasi ke Transaksi Pembayaran terkait
     */
    public function transaksiPembayaran(): BelongsTo
    {
        return $this->belongsTo(TransaksiPembayaran::class, 'transaksi_pembayaran_id');
    }
}
