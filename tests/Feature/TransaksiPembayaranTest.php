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

class TransaksiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Institusi $institusi;
    protected Mahasiswa $mahasiswa;
    protected PeriodeAkademik $periode;
    protected TagihanUkt $tagihan;

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
            'email'    => 'admin_trx_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'institusi_id'          => $this->institusi->id,
            'nomor_induk_mahasiswa' => '2024TEST001',
            'nama_lengkap'          => 'Mahasiswa Tester',
        ]);

        $this->periode = PeriodeAkademik::create([
            'institusi_id'    => $this->institusi->id,
            'tahun_akademik'  => '2024/2025',
            'semester'        => 'Ganjil',
            'tanggal_mulai'   => '2024-09-01',
            'tanggal_selesai' => '2025-02-28',
        ]);

        $this->tagihan = TagihanUkt::create([
            'institusi_id'        => $this->institusi->id,
            'mahasiswa_id'        => $this->mahasiswa->id,
            'periode_akademik_id' => $this->periode->id,
            'nomor_tagihan'       => 'INV-TEST-001',
            'nominal_tagihan'     => 5000000,
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
        ]);
    }

    public function test_transaksi_index_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('transaksi-pembayarans.index'));
        $response->assertStatus(200);
        $response->assertSee('Transaksi Pembayaran UKT');
    }

    public function test_can_create_payment_transaction(): void
    {
        $response = $this->actingAs($this->admin)->post(route('transaksi-pembayarans.store'), [
            'tagihan_ukt_id'    => $this->tagihan->id,
            'nominal_rupiah'    => 2000000,
            'metode_pembayaran' => 'VA',
            'status'            => 'berhasil',
        ]);

        $response->assertRedirect(route('transaksi-pembayarans.index'));
        $this->assertDatabaseHas('transaksi_pembayarans', [
            'tagihan_ukt_id' => $this->tagihan->id,
            'nominal_rupiah' => 2000000,
            'status'         => 'berhasil',
        ]);

        // Tagihan UKT status should update to dicicil
        $this->tagihan->refresh();
        $this->assertEquals('dicicil', $this->tagihan->status);
    }

    public function test_can_update_transaction_status(): void
    {
        $trx = TransaksiPembayaran::create([
            'institusi_id'           => $this->institusi->id,
            'tagihan_ukt_id'         => $this->tagihan->id,
            'gateway_transaction_id' => 'TRX-TEST-001',
            'nominal_rupiah'         => 5000000,
            'metode_pembayaran'      => 'QRIS',
            'status'                 => 'menunggu',
        ]);

        $response = $this->actingAs($this->admin)->put(route('transaksi-pembayarans.update', $trx->id), [
            'nominal_rupiah'    => 5000000,
            'metode_pembayaran' => 'QRIS',
            'status'            => 'berhasil',
        ]);

        $response->assertRedirect(route('transaksi-pembayarans.index'));
        $trx->refresh();
        $this->assertEquals('berhasil', $trx->status);
        $this->assertNotNull($trx->dibayar_pada);

        // Tagihan UKT should now be lunas since 5.000.000 is paid
        $this->tagihan->refresh();
        $this->assertEquals('lunas', $this->tagihan->status);
    }

    public function test_can_delete_transaction(): void
    {
        $trx = TransaksiPembayaran::create([
            'institusi_id'           => $this->institusi->id,
            'tagihan_ukt_id'         => $this->tagihan->id,
            'gateway_transaction_id' => 'TRX-TEST-DEL',
            'nominal_rupiah'         => 1000000,
            'metode_pembayaran'      => 'VA',
            'status'                 => 'berhasil',
            'dibayar_pada'           => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('transaksi-pembayarans.destroy', $trx->id));
        $response->assertRedirect(route('transaksi-pembayarans.index'));

        $this->assertDatabaseMissing('transaksi_pembayarans', [
            'id' => $trx->id,
        ]);
    }
}
