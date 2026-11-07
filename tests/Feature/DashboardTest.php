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

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Institusi $kampusA;
    protected Institusi $kampusB;
    protected PeriodeAkademik $periode;
    protected User $superAdmin;
    protected User $adminKampusA;
    protected User $userMahasiswaA;
    protected Mahasiswa $mahasiswaA;
    protected TagihanUkt $tagihanA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kampusA = Institusi::create([
            'kode_institusi' => 'UNISEV',
            'nama_institusi' => 'Universitas Sevima Nusantara',
            'secret_key'     => 'secret_kampus_a',
            'status'         => true,
        ]);

        $this->kampusB = Institusi::create([
            'kode_institusi' => 'POLITEK',
            'nama_institusi' => 'Politeknik Edukasi',
            'secret_key'     => 'secret_kampus_b',
            'status'         => true,
        ]);

        $this->periode = PeriodeAkademik::create([
            'institusi_id'    => $this->kampusA->id,
            'tahun_akademik'  => '2024/2025',
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
        ]);

        $this->superAdmin = User::create([
            'name'         => 'Super Admin Test',
            'email'        => 'superadmin.test@sevima.com',
            'password'     => bcrypt('password'),
            'role'         => 'admin',
            'institusi_id' => null,
        ]);

        $this->adminKampusA = User::create([
            'name'         => 'Admin Kampus A Test',
            'email'        => 'admin.a@sevima.com',
            'password'     => bcrypt('password'),
            'role'         => 'admin',
            'institusi_id' => $this->kampusA->id,
        ]);

        $this->userMahasiswaA = User::create([
            'name'         => 'Mahasiswa A Test',
            'email'        => 'mhs.a@sevima.com',
            'password'     => bcrypt('password'),
            'role'         => 'mahasiswa',
            'institusi_id' => $this->kampusA->id,
        ]);

        $this->mahasiswaA = Mahasiswa::create([
            'institusi_id'            => $this->kampusA->id,
            'user_id'                 => $this->userMahasiswaA->id,
            'nomor_induk_mahasiswa'   => 'NIM-2024-001',
            'nama_lengkap'            => 'Mahasiswa A Test',
        ]);

        $this->tagihanA = TagihanUkt::create([
            'institusi_id'        => $this->kampusA->id,
            'mahasiswa_id'        => $this->mahasiswaA->id,
            'periode_akademik_id' => $this->periode->id,
            'dibuat_oleh'         => $this->adminKampusA->id,
            'nomor_tagihan'       => 'INV-UNISEV-2024-00001',
            'nominal_tagihan'     => 5000000,
            'status'              => 'dicicil',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);

        TransaksiPembayaran::create([
            'institusi_id'           => $this->kampusA->id,
            'tagihan_ukt_id'         => $this->tagihanA->id,
            'gateway_transaction_id' => 'TRX-TEST-001',
            'nominal_rupiah'         => 2000000,
            'metode_pembayaran'      => 'VA',
            'status'                 => 'berhasil',
            'dibayar_pada'           => now(),
        ]);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_sees_global_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Super Administrator');
        $response->assertSee('Universitas Sevima Nusantara');
        $response->assertSee('Politeknik Edukasi');
        $response->assertSee('Rp 2.000.000'); // Dana masuk berhasil
        $response->assertSee('TRX-TEST-001');

        // Messages icon & dummy messages removed
        $response->assertDontSee('Maria Hudson');
        $response->assertDontSee('bi-chat-left-text');
    }

    public function test_finance_admin_sees_campus_specific_dashboard(): void
    {
        $response = $this->actingAs($this->adminKampusA)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Keuangan Kampus');
        $response->assertSee('Universitas Sevima Nusantara');
        $response->assertSee('UNISEV');
        $response->assertSee('Rp 2.000.000'); // Dana terkumpul
        $response->assertSee('Mahasiswa A Test');
        // Admin Kampus A should not see Kampus B name
        $response->assertDontSee('Politeknik Edukasi');

        // Messages icon & dummy messages removed
        $response->assertDontSee('Maria Hudson');
        $response->assertDontSee('bi-chat-left-text');
    }

    public function test_mahasiswa_sees_student_tuition_portal_dashboard(): void
    {
        $response = $this->actingAs($this->userMahasiswaA)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Portal Tagihan Mahasiswa');
        $response->assertSee('Mahasiswa A Test');
        $response->assertSee('NIM-2024-001');
        $response->assertSee('INV-UNISEV-2024-00001');
        $response->assertSee('Rp 5.000.000'); // Total Tagihan
        $response->assertSee('Rp 2.000.000'); // Sudah Dibayar
        $response->assertSee('Rp 3.000.000'); // Sisa Tagihan
        $response->assertSee('Bayar / Cicil Bulan Ini');

        // Messages icon & dummy messages removed
        $response->assertDontSee('Maria Hudson');
        $response->assertDontSee('bi-chat-left-text');
        // Contextual notification present
        $response->assertSee('Pembayaran Berhasil');
    }
}
