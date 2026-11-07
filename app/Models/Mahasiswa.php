<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswas';

    protected $fillable = [
        'institusi_id',
        'user_id',
        'nomor_induk_mahasiswa',
        'nama_lengkap',
    ];

    /**
     * Relasi ke Institusi Kampus
     */
    public function institusi(): BelongsTo
    {
        return $this->belongsTo(Institusi::class, 'institusi_id');
    }

    /**
     * Relasi ke Akun Pengguna (User Login)
     * Catatan ERD: Satu akun pengguna mahasiswa memiliki satu profil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Tagihan UKT
     */
    public function tagihanUkts(): HasMany
    {
        return $this->hasMany(TagihanUkt::class, 'mahasiswa_id');
    }
}
