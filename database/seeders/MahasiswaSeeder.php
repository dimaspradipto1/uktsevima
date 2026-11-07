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

        $userDimas = User::where('email', 'dimas@gmail.com')->first();
        $userSiti = User::where('email', 'siti@gmail.com')->first();
        $userBudi = User::where('email', 'budi@gmail.com')->first();
        $userRina = User::where('email', 'rina@gmail.com')->first();

        // Update institusi_id di user agar sesuai kampus mereka
        if ($institusi1) {
            $userDimas?->update(['institusi_id' => $institusi1->id]);
            $userSiti?->update(['institusi_id' => $institusi1->id]);

            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi1->id,
                    'nomor_induk_mahasiswa' => '2024010001',
                ],
                [
                    'nama_lengkap' => 'Dimas Pradipto',
                    'user_id'      => $userDimas?->id,
                ]
            );

            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi1->id,
                    'nomor_induk_mahasiswa' => '2024010002',
                ],
                [
                    'nama_lengkap' => 'Siti Nurhaliza',
                    'user_id'      => $userSiti?->id,
                ]
            );
        }

        if ($institusi2) {
            $userBudi?->update(['institusi_id' => $institusi2->id]);
            $userRina?->update(['institusi_id' => $institusi2->id]);

            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi2->id,
                    'nomor_induk_mahasiswa' => '2024020001',
                ],
                [
                    'nama_lengkap' => 'Budi Santoso',
                    'user_id'      => $userBudi?->id,
                ]
            );

            Mahasiswa::updateOrCreate(
                [
                    'institusi_id' => $institusi2->id,
                    'nomor_induk_mahasiswa' => '2024020002',
                ],
                [
                    'nama_lengkap' => 'Rina Andriani',
                    'user_id'      => $userRina?->id,
                ]
            );
        }
    }
}
