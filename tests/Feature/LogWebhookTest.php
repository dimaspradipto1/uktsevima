<?php

namespace Tests\Feature;

use App\Models\Institusi;
use App\Models\LogWebhook;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Institusi $institusi;
    protected TransaksiPembayaran $transaksi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institusi = Institusi::create([
            'kode_institusi' => 'UNIVTEST',
            'nama_institusi' => 'Universitas Uji Coba',
            'secret_key'     => 'secret_key_testing_123',
            'status'         => true,
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Tester',
            'email'    => 'admin_wh_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $mhs = Mahasiswa::create([
            'institusi_id'          => $this->institusi->id,
            'nomor_induk_mahasiswa' => '2024WH001',
            'nama_lengkap'          => 'Mahasiswa Webhook',
        ]);

        $periode = PeriodeAkademik::create([
            'institusi_id'    => $this->institusi->id,
            'tahun_akademik'  => '2024/2025',
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
        ]);

        $tagihan = TagihanUkt::create([
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $mhs->id,
            'periode_akademik_id' => $periode->id,
            'nomor_tagihan'       => 'INV-WH-001',
            'nominal_tagihan'     => 3000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        $this->transaksi = TransaksiPembayaran::create([
            'institusi_id'           => $this->institusi->id,
            'tagihan_ukt_id'         => $tagihan->id,
            'gateway_transaction_id' => 'TRX-WH-001',
            'nominal_rupiah'         => 3000000,
            'metode_pembayaran'      => 'VA',
            'status'                 => 'menunggu',
        ]);
    }

    public function test_log_webhook_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('log-webhooks.index'));
        $response->assertStatus(200);
        $response->assertSee('Log Webhook Payment Gateway');
    }

    public function test_can_simulate_webhook_via_admin(): void
    {
        $response = $this->actingAs($this->admin)->post(route('log-webhooks.store'), [
            'institusi_id'            => $this->institusi->id,
            'transaksi_pembayaran_id' => $this->transaksi->id,
            'jenis_event'             => 'payment.success',
            'simulasi_signature'      => 'valid',
        ]);

        $response->assertRedirect(route('log-webhooks.index'));
        $this->assertDatabaseHas('log_webhooks', [
            'institusi_id'       => $this->institusi->id,
            'signature_valid'    => true,
            'status_pemprosesan' => 'diproses',
        ]);

        // Transaction should be updated to berhasil
        $this->transaksi->refresh();
        $this->assertEquals('berhasil', $this->transaksi->status);
    }

    public function test_live_webhook_endpoint_processes_valid_hmac_signature(): void
    {
        $payload = [
            'event_id'               => 'EVT-TEST-LIVE-001',
            'event'                  => 'payment.success',
            'kode_institusi'         => $this->institusi->kode_institusi,
            'gateway_transaction_id' => $this->transaksi->gateway_transaction_id,
            'nominal_bayar'          => 3000000,
            'status'                 => 'berhasil',
        ];

        $rawBody = json_encode($payload);
        $validSignature = hash_hmac('sha256', $rawBody, $this->institusi->secret_key);

        $response = $this->postJson(route('webhook.incoming'), $payload, [
            'X-Institusi-Code'    => $this->institusi->kode_institusi,
            'X-Webhook-Signature' => $validSignature,
            'X-Gateway-Event-Id'  => 'EVT-TEST-LIVE-001',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('log_webhooks', [
            'gateway_event_id'   => 'EVT-TEST-LIVE-001',
            'signature_valid'    => true,
            'status_pemprosesan' => 'diproses',
        ]);

        $this->transaksi->refresh();
        $this->assertEquals('berhasil', $this->transaksi->status);
    }

    public function test_live_webhook_endpoint_rejects_invalid_hmac_signature(): void
    {
        $payload = [
            'event_id'               => 'EVT-TEST-LIVE-INVALID',
            'event'                  => 'payment.success',
            'kode_institusi'         => $this->institusi->kode_institusi,
            'gateway_transaction_id' => $this->transaksi->gateway_transaction_id,
        ];

        $response = $this->postJson(route('webhook.incoming'), $payload, [
            'X-Institusi-Code'    => $this->institusi->kode_institusi,
            'X-Webhook-Signature' => 'fake_invalid_signature',
            'X-Gateway-Event-Id'  => 'EVT-TEST-LIVE-INVALID',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('log_webhooks', [
            'gateway_event_id'   => 'EVT-TEST-LIVE-INVALID',
            'signature_valid'    => false,
            'status_pemprosesan' => 'ditolak',
            'kode_respons_http'  => 401,
        ]);
    }

    public function test_can_update_and_delete_log_webhook(): void
    {
        $log = LogWebhook::create([
            'institusi_id'       => $this->institusi->id,
            'gateway_event_id'   => 'EVT-MANUAL-001',
            'jenis_event'        => 'payment.success',
            'signature_valid'    => true,
            'status_pemprosesan' => 'diterima',
            'kode_respons_http'  => 200,
        ]);

        // Test update
        $updateResponse = $this->actingAs($this->admin)->put(route('log-webhooks.update', $log->id), [
            'status_pemprosesan' => 'diproses',
            'kode_respons_http'  => 200,
            'pesan_kesalahan'    => 'Diperbarui secara manual.',
        ]);
        $updateResponse->assertRedirect(route('log-webhooks.index'));
        $log->refresh();
        $this->assertEquals('diproses', $log->status_pemprosesan);

        // Test delete
        $deleteResponse = $this->actingAs($this->admin)->delete(route('log-webhooks.destroy', $log->id));
        $deleteResponse->assertRedirect(route('log-webhooks.index'));
        $this->assertDatabaseMissing('log_webhooks', ['id' => $log->id]);
    }
}

