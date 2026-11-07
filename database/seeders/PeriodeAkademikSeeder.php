<?php

namespace Database\Seeders;

use App\Models\Institusi;
use App\Models\PeriodeAkademik;
use Illuminate\Database\Seeder;

class PeriodeAkademikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institusi1 = Institusi::where('kode_institusi', 'UNIV01')->first() ?? Institusi::first();
        $institusi2 = Institusi::where('kode_institusi', 'POLI02')->first();

        if ($institusi1) {
            PeriodeAkademik::firstOrCreate(
                [
                    'institusi_id'   => $institusi1->id,
                    'tahun_akademik' => '2026/2027',
                    'semester'       => 'Ganjil',
                ],
                [
                    'tanggal_mulai'   => '2026-09-01',
                    'tanggal_selesai' => '2027-01-31',
                ]
            );

            PeriodeAkademik::firstOrCreate(
                [
                    'institusi_id'   => $institusi1->id,
                    'tahun_akademik' => '2025/2026',
                    'semester'       => 'Genap',
                ],
                [
                    'tanggal_mulai'   => '2026-02-01',
                    'tanggal_selesai' => '2026-07-31',
                ]
            );
        }

        if ($institusi2) {
            PeriodeAkademik::firstOrCreate(
                [
                    'institusi_id'   => $institusi2->id,
                    'tahun_akademik' => '2026/2027',
                    'semester'       => 'Ganjil',
                ],
                [
                    'tanggal_mulai'   => '2026-09-01',
                    'tanggal_selesai' => '2027-01-31',
                ]
            );
        }
    }
}
