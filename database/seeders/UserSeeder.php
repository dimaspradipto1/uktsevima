<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Administrator',
                'email'    => 'admin@gmail.com',
                'password' => 'admin123',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Dimas Pradipto',
                'email'    => 'dimas@gmail.com',
                'password' => 'admin123',
                'role'     => 'mahasiswa',
            ],
            [
                'name'     => 'Siti Nurhaliza',
                'email'    => 'siti@gmail.com',
                'password' => 'admin123',
                'role'     => 'mahasiswa',
            ],
            [
                'name'     => 'Budi Santoso',
                'email'    => 'budi@gmail.com',
                'password' => 'admin123',
                'role'     => 'mahasiswa',
            ],
            [
                'name'     => 'Rina Andriani',
                'email'    => 'rina@gmail.com',
                'password' => 'admin123',
                'role'     => 'mahasiswa',
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
