<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeAkademik extends Model
{
    use HasFactory;

    protected $table = 'periode_akademiks';

    protected $fillable = [
        'institusi_id',
        'tahun_akademik',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai'   => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    /**
     * Relasi ke Institusi (Tenant Kampus)
     */
    public function institusi(): BelongsTo
    {
        return $this->belongsTo(Institusi::class, 'institusi_id');
    }

    /**
     * Relasi ke Tagihan UKT
     */
    public function tagihanUkts(): HasMany
    {
        return $this->hasMany(TagihanUkt::class, 'periode_akademik_id');
    }

    /**
     * Label gabungan tahun akademik dan semester
     */
    public function getNamaPeriodeAttribute(): string
    {
        return "{$this->tahun_akademik} - {$this->semester}";
    }
}
