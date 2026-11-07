<?php

namespace Database\Seeders;

use App\Models\Institusi;
use App\Models\LogWebhook;
use App\Models\TransaksiPembayaran;
use Illuminate\Database\Seeder;

class LogWebhookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institusi1 = Institusi::first();
        $transaksi1 = TransaksiPembayaran::first();

        if ($institusi1) {
            // 1. Log Webhook Valid (HMAC Sesuai)
            $rawPayload1 = json_encode([
                'event_id'               => 'EVT-GATEWAY-2024-0001',
                'event'                  => 'payment.success',
                'kode_institusi'         => $institusi1->kode_institusi,
                'gateway_transaction_id' => $transaksi1?->gateway_transaction_id ?? 'TRX-UNIV01-2024-001',
                'nominal_bayar'          => 1500000,
                'status'                 => 'berhasil',
                'timestamp'              => now()->subDays(5)->toIso8601String(),
            ]);

            $validSignature = hash_hmac('sha256', $rawPayload1, $institusi1->secret_key);

            LogWebhook::updateOrCreate(
                ['gateway_event_id' => 'EVT-GATEWAY-2024-0001'],
                [
                    'institusi_id'            => $institusi1->id,
                    'transaksi_pembayaran_id' => $transaksi1?->id,
                    'kode_institusi_diterima' => $institusi1->kode_institusi,
                    'jenis_event'             => 'payment.success',
                    'signature_diterima'      => $validSignature,
                    'signature_valid'         => true,
                    'payload_asli'            => $rawPayload1,
                    'payload_json'            => json_decode($rawPayload1, true),
                    'status_pemprosesan'      => 'diproses',
                    'kode_respons_http'       => 200,
                    'alamat_ip'               => '103.247.8.12',
                    'pesan_kesalahan'         => 'Webhook valid dan berhasil mengupdate status transaksi pembayaran.',
                    'diterima_pada'           => now()->subDays(5),
                    'diproses_pada'           => now()->subDays(5),
                ]
            );

            // 2. Log Webhook Invalid Signature (Ditolak)
            $rawPayload2 = json_encode([
                'event_id'               => 'EVT-GATEWAY-2024-0002',
                'event'                  => 'payment.success',
                'kode_institusi'         => $institusi1->kode_institusi,
                'gateway_transaction_id' => 'TRX-UNKNOWN-999',
                'nominal_bayar'          => 5000000,
                'status'                 => 'berhasil',
            ]);

            LogWebhook::updateOrCreate(
                ['gateway_event_id' => 'EVT-GATEWAY-2024-0002'],
                [
                    'institusi_id'            => $institusi1->id,
                    'transaksi_pembayaran_id' => null,
                    'kode_institusi_diterima' => $institusi1->kode_institusi,
                    'jenis_event'             => 'payment.success',
                    'signature_diterima'      => 'invalid_signature_hash_example123',
                    'signature_valid'         => false,
                    'payload_asli'            => $rawPayload2,
                    'payload_json'            => json_decode($rawPayload2, true),
                    'status_pemprosesan'      => 'ditolak',
                    'kode_respons_http'       => 401,
                    'alamat_ip'               => '192.168.1.100',
                    'pesan_kesalahan'         => 'Invalid HMAC-SHA256 signature.',
                    'diterima_pada'           => now()->subDays(2),
                    'diproses_pada'           => now()->subDays(2),
                ]
            );

            // 3. Log Webhook Duplikat (Diabaikan)
            LogWebhook::updateOrCreate(
                [
                    'gateway_event_id' => 'EVT-GATEWAY-2024-0001',
                    'status_pemprosesan' => 'duplikat',
                ],
                [
                    'institusi_id'            => $institusi1->id,
                    'transaksi_pembayaran_id' => $transaksi1?->id,
                    'kode_institusi_diterima' => $institusi1->kode_institusi,
                    'jenis_event'             => 'payment.success',
                    'signature_diterima'      => $validSignature,
                    'signature_valid'         => true,
                    'payload_asli'            => $rawPayload1,
                    'payload_json'            => json_decode($rawPayload1, true),
                    'status_pemprosesan'      => 'duplikat',
                    'kode_respons_http'       => 200,
                    'alamat_ip'               => '103.247.8.12',
                    'pesan_kesalahan'         => 'Event duplicate. Webhook already processed.',
                    'diterima_pada'           => now()->subDays(5)->addMinutes(1),
                    'diproses_pada'           => now()->subDays(5)->addMinutes(1),
                ]
            );
        }
    }
}
