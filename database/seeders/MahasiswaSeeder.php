<?php

namespace Database\Seeders;

use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institusi1 = Institusi::first();
        $institusi2 = Institusi::skip(1)->first() ?? $institusi1;
        $studentUser = User::where('role', 'mahasiswa')->first();

        if ($institusi1) {
            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi1->id,
                    'nomor_induk_mahasiswa' => '2024010001',
                ],
                [
                    'nama_lengkap' => 'Dimas Pradipto',
                    'user_id' => $studentUser?->id,
                ]
            );

            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi1->id,
                    'nomor_induk_mahasiswa' => '2024010002',
                ],
                [
                    'nama_lengkap' => 'Siti Nurhaliza',
                    'user_id' => null,
                ]
            );
        }

        if ($institusi2) {
            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi2->id,
                    'nomor_induk_mahasiswa' => '2024020001',
                ],
                [
                    'nama_lengkap' => 'Budi Santoso',
                    'user_id' => null,
                ]
            );

            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi2->id,
                    'nomor_induk_mahasiswa' => '2024020002',
                ],
                [
                    'nama_lengkap' => 'Rina Andriani',
                    'user_id' => null,
                ]
            );
        }
    }
}
