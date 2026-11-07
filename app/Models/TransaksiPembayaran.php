<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPembayaran extends Model
{
    use HasFactory;

    protected $table = 'transaksi_pembayarans';

    protected $fillable = [
        'institusi_id',
        'tagihan_ukt_id',
        'gateway_transaction_id',
        'nominal_rupiah',
        'metode_pembayaran',
        'status', // menunggu, berhasil, gagal, kedaluwarsa
        'dibayar_pada',
    ];

    protected function casts(): array
    {
        return [
            'nominal_rupiah' => 'integer',
            'dibayar_pada'   => 'datetime',
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
     * Relasi ke Log Webhook (ERD)
     */
    public function logWebhooks(): HasMany
    {
        return $this->hasMany(LogWebhook::class, 'transaksi_pembayaran_id');
    }

    /**
     * Alias nominal_bayar untuk kompatibilitas
     */
    public function getNominalBayarAttribute(): int
    {
        return (int) $this->nominal_rupiah;
    }

    public function setNominalBayarAttribute($value): void
    {
        $this->attributes['nominal_rupiah'] = $value;
    }

    /**
     * Format Rupiah Nominal Pembayaran
     */
    public function getFormatNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal_rupiah, 0, ',', '.');
    }

    public function getFormatNominalBayarAttribute(): string
    {
        return $this->getFormatNominalAttribute();
    }
}
