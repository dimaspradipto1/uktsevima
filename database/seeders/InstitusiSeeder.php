<?php

namespace Database\Seeders;

use App\Models\Institusi;
use Illuminate\Database\Seeder;

class InstitusiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institusis = [
            [
                'kode_institusi' => 'UNIV01',
                'nama_institusi' => 'Universitas Sevima Nusantara',
                'secret_key'     => 'e4d909c290d0fb1ca068ffaddf22cbd0add0b7b12d5930219c00b58e65842813',
                'status'         => true,
            ],
            [
                'kode_institusi' => 'POLI02',
                'nama_institusi' => 'Politeknik Negeri Teknologi Edukasi',
                'secret_key'     => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
                'status'         => true,
            ],
            [
                'kode_institusi' => 'STMIK03',
                'nama_institusi' => 'STMIK Cipta Digital Edukasi',
                'secret_key'     => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',
                'status'         => false,
            ],
        ];

        foreach ($institusis as $data) {
            Institusi::firstOrCreate(['kode_institusi' => $data['kode_institusi']], $data);
        }
    }
}

