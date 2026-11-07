<?php

namespace Database\Seeders;

use App\Models\Institusi;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institusi1 = Institusi::where('kode_institusi', 'UNIV01')->first() ?? Institusi::first();
        $institusi2 = Institusi::where('kode_institusi', 'POLI02')->first() ?? Institusi::skip(1)->first();
        $institusi3 = Institusi::where('kode_institusi', 'STMIK03')->first() ?? Institusi::skip(2)->first();

        $users = [
            // 1. Super Admin (Global Platform)
            [
                'name'         => 'Super Administrator Sevima',
                'email'        => 'admin@gmail.com',
                'password'     => 'admin123',
                'role'         => 'admin',
                'institusi_id' => null,
            ],
            [
                'name'         => 'Super Admin SEVIMA',
                'email'        => 'superadmin@sevima.com',
                'password'     => 'admin123',
                'role'         => 'admin',
                'institusi_id' => null,
            ],

            // 2. Finance Admin per Kampus (Tenant Scoped)
            [
                'name'         => 'Finance Admin Univ Sevima',
                'email'        => 'admin.univ@sevima.com',
                'password'     => 'admin123',
                'role'         => 'admin',
                'institusi_id' => $institusi1?->id,
            ],
            [
                'name'         => 'Finance Admin Politeknik',
                'email'        => 'admin.poli@sevima.com',
                'password'     => 'admin123',
                'role'         => 'admin',
                'institusi_id' => $institusi2?->id,
            ],
            [
                'name'         => 'Finance Admin STMIK',
                'email'        => 'admin.stmik@sevima.com',
                'password'     => 'admin123',
                'role'         => 'admin',
                'institusi_id' => $institusi3?->id,
            ],

            // 3. Akun Mahasiswa
            [
                'name'         => 'Dimas Pradipto',
                'email'        => 'dimas@gmail.com',
                'password'     => 'admin123',
                'role'         => 'mahasiswa',
                'institusi_id' => $institusi1?->id,
            ],
            [
                'name'         => 'Siti Nurhaliza',
                'email'        => 'siti@gmail.com',
                'password'     => 'admin123',
                'role'         => 'mahasiswa',
                'institusi_id' => $institusi1?->id,
            ],
            [
                'name'         => 'Budi Santoso',
                'email'        => 'budi@gmail.com',
                'password'     => 'admin123',
                'role'         => 'mahasiswa',
                'institusi_id' => $institusi2?->id,
            ],
            [
                'name'         => 'Rina Andriani',
                'email'        => 'rina@gmail.com',
                'password'     => 'admin123',
                'role'         => 'mahasiswa',
                'institusi_id' => $institusi2?->id,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
