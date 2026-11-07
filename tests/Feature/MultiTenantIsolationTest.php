<?php

namespace Tests\Feature;

use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Institusi $kampusA;
    protected Institusi $kampusB;
    protected User $adminKampusA;
    protected User $adminKampusB;
    protected Mahasiswa $mhsKampusA;
    protected Mahasiswa $mhsKampusB;
    protected TagihanUkt $tagihanKampusA;
    protected TagihanUkt $tagihanKampusB;
    protected TransaksiPembayaran $transaksiKampusB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Institusi A
        $this->kampusA = Institusi::create([
            'kode_institusi' => 'KAMPUS_A',
            'nama_institusi' => 'Universitas Kampus A',
            'secret_key'     => 'secret_key_kampus_a_123',
            'status'         => true,
        ]);

        // 2. Institusi B
        $this->kampusB = Institusi::create([
            'kode_institusi' => 'KAMPUS_B',
            'nama_institusi' => 'Institut Kampus B',
            'secret_key'     => 'secret_key_kampus_b_456',
            'status'         => true,
        ]);

        // Admin Kampus A
        $this->adminKampusA = User::create([
            'name'         => 'Admin Kampus A',
            'email'        => 'admin.a@kampusa.ac.id',
            'password'     => bcrypt('password'),
            'role'         => 'admin',
            'institusi_id' => $this->kampusA->id,
        ]);

        // Admin Kampus B
        $this->adminKampusB = User::create([
            'name'         => 'Admin Kampus B',
            'email'        => 'admin.b@kampusb.ac.id',
            'password'     => bcrypt('password'),
            'role'         => 'admin',
            'institusi_id' => $this->kampusB->id,
        ]);

        // Periode
        $periodeA = PeriodeAkademik::create([
            'institusi_id'    => $this->kampusA->id,
            'tahun_akademik'  => '2024/2025',
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
        ]);

        $periodeB = PeriodeAkademik::create([
            'institusi_id'    => $this->kampusB->id,
            'tahun_akademik'  => '2024/2025',
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
        ]);

        // Mahasiswa Kampus A & B
        $this->mhsKampusA = Mahasiswa::create([
            'institusi_id'          => $this->kampusA->id,
            'nomor_induk_mahasiswa' => '2024010001',
            'nama_lengkap'          => 'Mahasiswa Kampus A',
        ]);

        $this->mhsKampusB = Mahasiswa::create([
            'institusi_id'          => $this->kampusB->id,
            'nomor_induk_mahasiswa' => '2024020001',
            'nama_lengkap'          => 'Mahasiswa Kampus B',
        ]);

        // Tagihan Kampus A & B
        $this->tagihanKampusA = TagihanUkt::create([
            'institusi_id'        => $this->kampusA->id,
            'mahasiswa_id'        => $this->mhsKampusA->id,
            'periode_akademik_id' => $periodeA->id,
            'nomor_tagihan'       => 'INV-A-001',
            'nominal_tagihan'     => 5000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        $this->tagihanKampusB = TagihanUkt::create([
            'institusi_id'        => $this->kampusB->id,
            'mahasiswa_id'        => $this->mhsKampusB->id,
            'periode_akademik_id' => $periodeB->id,
            'nomor_tagihan'       => 'INV-B-001',
            'nominal_tagihan'     => 4000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        $this->transaksiKampusB = TransaksiPembayaran::create([
            'institusi_id'           => $this->kampusB->id,
            'tagihan_ukt_id'         => $this->tagihanKampusB->id,
            'gateway_transaction_id' => 'TRX-B-001',
            'nominal_rupiah'         => 2000000,
            'metode_pembayaran'      => 'VA',
            'status'                 => 'berhasil',
            'dibayar_pada'           => now(),
        ]);
    }

    public function test_admin_kampus_a_cannot_view_tagihan_kampus_b(): void
    {
        // Admin Kampus A mencoba melihat detail tagihan milik Kampus B
        $response = $this->actingAs($this->adminKampusA)->get(route('tagihan-ukts.show', $this->tagihanKampusB->id));
        $response->assertStatus(403);
    }

    public function test_admin_kampus_a_cannot_update_tagihan_kampus_b(): void
    {
        // Admin Kampus A mencoba mengubah tagihan milik Kampus B
        $response = $this->actingAs($this->adminKampusA)->put(route('tagihan-ukts.update', $this->tagihanKampusB->id), [
            'nominal_tagihan'     => 9999999,
            'tanggal_jatuh_tempo' => now()->addDays(60)->format('Y-m-d'),
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_kampus_a_cannot_delete_transaksi_kampus_b(): void
    {
        // Admin Kampus A mencoba menghapus transaksi milik Kampus B
        $response = $this->actingAs($this->adminKampusA)->delete(route('transaksi-pembayarans.destroy', $this->transaksiKampusB->id));
        $response->assertStatus(403);
    }

    public function test_admin_kampus_a_cannot_edit_mahasiswa_kampus_b(): void
    {
        // Admin Kampus A mencoba mengedit profil mahasiswa Kampus B
        $response = $this->actingAs($this->adminKampusA)->get(route('mahasiswas.edit', $this->mhsKampusB->id));
        $response->assertStatus(403);
    }

    public function test_student_api_endpoint_returns_correct_tuition_and_transactions(): void
    {
        $response = $this->getJson(route('api.mahasiswa.tagihan', $this->mhsKampusB->nomor_induk_mahasiswa));

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.mahasiswa.nim', $this->mhsKampusB->nomor_induk_mahasiswa);
        $response->assertJsonPath('data.mahasiswa.institusi', 'Institut Kampus B');
        $response->assertJsonPath('data.tagihan_aktif.nomor_tagihan', 'INV-B-001');
    }

    public function test_webhook_with_secret_key_kampus_a_is_rejected_for_kampus_b(): void
    {
        $payload = [
            'event_id'               => 'EVT-TEST-ISOLATION-01',
            'event'                  => 'payment.success',
            'kode_institusi'         => $this->kampusB->kode_institusi, // Ditujukan untuk Kampus B
            'gateway_transaction_id' => $this->transaksiKampusB->gateway_transaction_id,
            'nominal_bayar'          => 2000000,
        ];

        $rawBody = json_encode($payload);

        // Disahkan memakai secret key Kampus A (Cross-tenant forgery attempt)
        $forgedSignature = hash_hmac('sha256', $rawBody, $this->kampusA->secret_key);

        $response = $this->postJson(route('webhook.incoming'), $payload, [
            'X-Institusi-Code' => $this->kampusB->kode_institusi,
            'X-Signature'      => $forgedSignature,
            'X-Gateway-Event-Id' => 'EVT-TEST-ISOLATION-01',
        ]);

        // Harus ditolak dengan HTTP 401 Unauthorized karena secret key tidak cocok dengan Kampus B
        $response->assertStatus(401);
    }
}

