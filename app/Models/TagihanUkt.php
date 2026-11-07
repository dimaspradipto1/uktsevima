<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TagihanUkt extends Model
{
    use HasFactory;

    protected $table = 'tagihan_ukts';

    protected $fillable = [
        'institusi_id',
        'mahasiswa_id',
        'periode_akademik_id',
        'dibuat_oleh',
        'nomor_tagihan',
        'nominal_tagihan',
        'status', // belum_bayar, dicicil, lunas, kadaluarsa
        'diterbitkan_pada',
        'tanggal_jatuh_tempo',
        'dibayar_pada',
    ];

    protected function casts(): array
    {
        return [
            'diterbitkan_pada'    => 'datetime',
            'tanggal_jatuh_tempo' => 'datetime',
            'dibayar_pada'        => 'datetime',
            'nominal_tagihan'     => 'integer',
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
     * Relasi ke Profil Mahasiswa
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relasi ke Periode Akademik Semester
     */
    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_akademik_id');
    }

    /**
     * Relasi ke Pengguna (Admin Pembuat Tagihan)
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /**
     * Relasi ke Riwayat Transaksi Pembayaran / Cicilan
     */
    public function transaksiPembayarans(): HasMany
    {
        return $this->hasMany(TransaksiPembayaran::class, 'tagihan_ukt_id');
    }

    /**
     * Hitung akumulasi nominal cicilan yang sudah berhasil dibayar
     */
    public function getTotalDibayarAttribute(): int
    {
        return (int) $this->transaksiPembayarans()
            ->where('status', 'berhasil')
            ->sum('nominal_rupiah');
    }

    /**
     * Sisa tagihan UKT yang belum dibayarkan
     */
    public function getSisaTagihanAttribute(): int
    {
        return max(0, $this->nominal_tagihan - $this->total_dibayar);
    }

    /**
     * Persentase pelunasan tagihan UKT (0 - 100%)
     */
    public function getPersentaseLunasAttribute(): int
    {
        if ($this->nominal_tagihan <= 0) {
            return 100;
        }

        return min(100, (int) round(($this->total_dibayar / $this->nominal_tagihan) * 100));
    }

    /**
     * Format Rupiah Nominal Tagihan
     */
    public function getFormatNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal_tagihan, 0, ',', '.');
    }

    /**
     * Format Rupiah Total Terbayar
     */
    public function getFormatDibayarAttribute(): string
    {
        return 'Rp ' . number_format($this->total_dibayar, 0, ',', '.');
    }

    /**
     * Format Rupiah Sisa Tagihan
     */
    public function getFormatSisaAttribute(): string
    {
        return 'Rp ' . number_format($this->sisa_tagihan, 0, ',', '.');
    }
}
