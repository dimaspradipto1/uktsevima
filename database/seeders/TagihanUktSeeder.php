<?php

namespace Database\Seeders;

use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use App\Models\User;
use Illuminate\Database\Seeder;

class TagihanUktSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $mhs1 = Mahasiswa::where('nomor_induk_mahasiswa', '2024010001')->first();
        $mhs2 = Mahasiswa::where('nomor_induk_mahasiswa', '2024010002')->first();
        $mhs3 = Mahasiswa::where('nomor_induk_mahasiswa', '2024020001')->first();

        $periode1 = PeriodeAkademik::where('institusi_id', $mhs1?->institusi_id)->first();
        $periode2 = PeriodeAkademik::where('institusi_id', $mhs3?->institusi_id)->first() ?? $periode1;

        if ($mhs1 && $periode1) {
            // Tagihan 1: Status Dicicil (ada pembayaran bulan 1)
            $tagihan1 = TagihanUkt::updateOrCreate(
                [
                    'institusi_id'        => $mhs1->institusi_id,
                    'mahasiswa_id'        => $mhs1->id,
                    'periode_akademik_id' => $periode1->id,
                ],
                [
                    'dibuat_oleh'         => $admin?->id,
                    'nomor_tagihan'       => 'INV-UNIV01-2024-00001',
                    'nominal_tagihan'     => 5000000,
                    'status'              => 'dicicil',
                    'diterbitkan_pada'    => now()->subDays(15),
                    'tanggal_jatuh_tempo' => now()->addDays(45),
                    'dibayar_pada'        => null,
                ]
            );

            // Transaksi Cicilan Bulan Pertama
            TransaksiPembayaran::updateOrCreate(
                [
                    'institusi_id'           => $mhs1->institusi_id,
                    'tagihan_ukt_id'         => $tagihan1->id,
                    'gateway_transaction_id' => 'TRX-UNIV01-2024-001',
                ],
                [
                    'nominal_bayar'     => 1500000,
                    'metode_pembayaran' => 'VA',
                    'status'            => 'berhasil',
                    'dibayar_pada'      => now()->subDays(5),
                ]
            );
        }

        if ($mhs2 && $periode1) {
            // Tagihan 2: Status Belum Bayar
            TagihanUkt::updateOrCreate(
                [
                    'institusi_id'        => $mhs2->institusi_id,
                    'mahasiswa_id'        => $mhs2->id,
                    'periode_akademik_id' => $periode1->id,
                ],
                [
                    'dibuat_oleh'         => $admin?->id,
                    'nomor_tagihan'       => 'INV-UNIV01-2024-00002',
                    'nominal_tagihan'     => 4500000,
                    'status'              => 'belum_bayar',
                    'diterbitkan_pada'    => now()->subDays(10),
                    'tanggal_jatuh_tempo' => now()->addDays(50),
                    'dibayar_pada'        => null,
                ]
            );
        }

        if ($mhs3 && $periode2) {
            // Tagihan 3: Status Lunas
            $tagihan3 = TagihanUkt::updateOrCreate(
                [
                    'institusi_id'        => $mhs3->institusi_id,
                    'mahasiswa_id'        => $mhs3->id,
                    'periode_akademik_id' => $periode2->id,
                ],
                [
                    'dibuat_oleh'         => $admin?->id,
                    'nomor_tagihan'       => 'INV-POLI02-2024-00001',
                    'nominal_tagihan'     => 3000000,
                    'status'              => 'lunas',
                    'diterbitkan_pada'    => now()->subDays(20),
                    'tanggal_jatuh_tempo' => now()->addDays(40),
                    'dibayar_pada'        => now()->subDays(2),
                ]
            );

            TransaksiPembayaran::updateOrCreate(
                [
                    'institusi_id'           => $mhs3->institusi_id,
                    'tagihan_ukt_id'         => $tagihan3->id,
                    'gateway_transaction_id' => 'TRX-POLI02-2024-001',
                ],
                [
                    'nominal_bayar'     => 3000000,
                    'metode_pembayaran' => 'QRIS',
                    'status'            => 'berhasil',
                    'dibayar_pada'      => now()->subDays(2),
                ]
            );
        }
    }
}
