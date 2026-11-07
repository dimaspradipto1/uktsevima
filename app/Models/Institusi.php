<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institusi extends Model
{
    use HasFactory;

    protected $table = 'institusis';

    protected $fillable = [
        'kode_institusi',
        'nama_institusi',
        'secret_key',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Relasi ke Pengguna (User)
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'institusi_id');
    }

    /**
     * Relasi ke Mahasiswa
     */
    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'institusi_id');
    }

    /**
     * Relasi ke Periode Akademik
     */
    public function periodeAkademiks(): HasMany
    {
        return $this->hasMany(PeriodeAkademik::class, 'institusi_id');
    }

    /**
     * Relasi ke Tagihan UKT
     */
    public function tagihanUkts(): HasMany
    {
        return $this->hasMany(TagihanUkt::class, 'institusi_id');
    }

    /**
     * Relasi ke Transaksi Pembayaran
     */
    public function transaksiPembayarans(): HasMany
    {
        return $this->hasMany(TransaksiPembayaran::class, 'institusi_id');
    }

    /**
     * Relasi ke Log Webhook
     */
    public function logWebhooks(): HasMany
    {
        return $this->hasMany(LogWebhook::class, 'institusi_id');
    }
}
