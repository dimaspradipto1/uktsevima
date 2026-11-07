<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPembayaran extends Model
{
    use HasFactory;

    protected $table = 'transaksi_pembayarans';

    protected $fillable = [
        'institusi_id',
        'tagihan_ukt_id',
        'gateway_transaction_id',
        'nominal_bayar',
        'metode_pembayaran',
        'status', // menunggu, berhasil, gagal, kadaluarsa
        'dibayar_pada',
    ];

    protected function casts(): array
    {
        return [
            'nominal_bayar' => 'integer',
            'dibayar_pada'  => 'datetime',
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
     * Relasi ke Induk Tagihan UKT
     */
    public function tagihanUkt(): BelongsTo
    {
        return $this->belongsTo(TagihanUkt::class, 'tagihan_ukt_id');
    }

    /**
     * Format Rupiah Nominal Bayar
     */
    public function getFormatNominalBayarAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal_bayar, 0, ',', '.');
    }
}
