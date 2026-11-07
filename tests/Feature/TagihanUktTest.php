<?php

namespace Tests\Feature;

use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use App\Models\TagihanUkt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagihanUktTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $studentUser;
    protected Mahasiswa $mahasiswa;
    protected Institusi $institusi;
    protected PeriodeAkademik $periode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institusi = Institusi::create([
            'kode_institusi' => 'UNIVTEST',
            'nama_institusi' => 'Universitas Uji Coba',
            'secret_key'     => 'secret123456',
            'status'         => true,
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Finance',
            'email'    => 'admin_finance_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->studentUser = User::create([
            'name'     => 'Mahasiswa Tester',
            'email'    => 'mhs_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'mahasiswa',
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'institusi_id'           => $this->institusi->id,
            'user_id'                => $this->studentUser->id,
            'nomor_induk_mahasiswa'  => '2024TEST001',
            'nama_lengkap'           => 'Mahasiswa Tester',
        ]);

        $this->periode = PeriodeAkademik::create([
            'institusi_id'    => $this->institusi->id,
            'tahun_akademik'  => '2024/2025',
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
        ]);
    }

    public function test_admin_can_access_tagihan_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('tagihan-ukts.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Tagihan UKT');
    }

    public function test_mahasiswa_sees_student_portal_view(): void
    {
        // Buat tagihan aktif
        TagihanUkt::create([
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'dibuat_oleh'         => $this->admin->id,
            'nomor_tagihan'       => 'INV-TEST-001',
            'nominal_tagihan'     => 5000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->studentUser)->get(route('tagihan-ukts.index'));
        $response->assertStatus(200);
        $response->assertSee('Portal Tagihan UKT Mahasiswa');
        $response->assertSee('Tagihan Semester Berjalan');
        $response->assertSee('INV-TEST-001');
    }

    public function test_can_create_tagihan_ukt(): void
    {
        $response = $this->actingAs($this->admin)->post(route('tagihan-ukts.store'), [
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'nominal_tagihan'     => 6000000,
            'tanggal_jatuh_tempo' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tagihan-ukts.index'));
        $this->assertDatabaseHas('tagihan_ukts', [
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'nominal_tagihan'     => 6000000,
        ]);
    }

    public function test_campus_admin_create_tagihan_locks_institution(): void
    {
        $campusAdmin = User::create([
            'name'         => 'Admin Univ Only',
            'email'        => 'admin.univ.only@example.com',
            'password'     => bcrypt('password'),
            'role'         => 'admin',
            'institusi_id' => $this->institusi->id,
        ]);

        $response = $this->actingAs($campusAdmin)->get(route('tagihan-ukts.create'));
        $response->assertStatus(200);
        $response->assertSee('Terkunci otomatis pada institusi akun Anda.');
        $response->assertSee($this->institusi->nama_institusi);
        $response->assertDontSee('-- Pilih Institusi Kampus --');
    }

    public function test_can_batch_generate_tagihan_automatically_for_all_students(): void
    {
        // Buat mahasiswa kedua di institusi yang sama
        $mhs2 = Mahasiswa::create([
            'institusi_id'          => $this->institusi->id,
            'nomor_induk_mahasiswa' => '2024TEST002',
            'nama_lengkap'          => 'Mahasiswa Tester 2',
        ]);

        $periodeBaru = PeriodeAkademik::create([
            'institusi_id'    => $this->institusi->id,
            'tahun_akademik'  => '2025/2026',
            'semester'        => 'Genap',
            'tanggal_mulai'   => '2025-03-01',
            'tanggal_selesai' => '2025-08-31',
        ]);

        $response = $this->actingAs($this->admin)->post(route('tagihan-ukts.store'), [
            'mode_penerbitan'     => 'semua',
            'institusi_id'        => $this->institusi->id,
            'periode_akademik_id' => $periodeBaru->id,
            'nominal_tagihan'     => 4500000,
            'tanggal_jatuh_tempo' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tagihan-ukts.index'));

        // Harus terbuat tagihan untuk kedua mahasiswa
        $this->assertDatabaseHas('tagihan_ukts', [
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $periodeBaru->id,
            'nominal_tagihan'     => 4500000,
        ]);
        $this->assertDatabaseHas('tagihan_ukts', [
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $mhs2->id,
            'periode_akademik_id' => $periodeBaru->id,
            'nominal_tagihan'     => 4500000,
        ]);
    }

    public function test_rejects_duplicate_tagihan_for_same_semester(): void
    {
        TagihanUkt::create([
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'nomor_tagihan'       => 'INV-TEST-DUP-1',
            'nominal_tagihan'     => 5000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->admin)->from(route('tagihan-ukts.index'))->post(route('tagihan-ukts.store'), [
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'nominal_tagihan'     => 5000000,
            'tanggal_jatuh_tempo' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tagihan-ukts.index'));
        $response->assertSessionHasErrors(['mahasiswa_id']);
    }

    public function test_can_pay_monthly_installment_and_updates_status(): void
    {
        $tagihan = TagihanUkt::create([
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'nomor_tagihan'       => 'INV-TEST-INSTALLMENT',
            'nominal_tagihan'     => 3000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        // 1. Bayar cicilan bulan pertama Rp 1.000.000
        $response1 = $this->actingAs($this->studentUser)->post(route('tagihan-ukts.bayar', $tagihan->id), [
            'nominal_bayar'     => 1000000,
            'metode_pembayaran' => 'VA',
        ]);
        $response1->assertSessionHasNoErrors();
        $tagihan->refresh();
        $this->assertEquals('dicicil', $tagihan->status);
        $this->assertEquals(2000000, $tagihan->sisa_tagihan);

        // 2. Bayar cicilan kedua Rp 2.000.000 (Pelunasan)
        $response2 = $this->actingAs($this->studentUser)->post(route('tagihan-ukts.bayar', $tagihan->id), [
            'nominal_bayar'     => 2000000,
            'metode_pembayaran' => 'QRIS',
        ]);
        $response2->assertSessionHasNoErrors();
        $tagihan->refresh();
        $this->assertEquals('lunas', $tagihan->status);
        $this->assertEquals(0, $tagihan->sisa_tagihan);
        $this->assertNotNull($tagihan->dibayar_pada);

        // Cek log webhook otomatis tercatat di audit trail
        $this->assertDatabaseHas('log_webhooks', [
            'institusi_id'       => $this->institusi->id,
            'jenis_event'        => 'payment.success',
            'status_pemprosesan' => 'diproses',
            'signature_valid'    => true,
        ]);
    }
}

