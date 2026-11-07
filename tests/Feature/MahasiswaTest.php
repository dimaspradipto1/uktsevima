<?php

namespace Tests\Feature;

use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Institusi $institusi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institusi = Institusi::create([
            'kode_institusi' => 'TEST01',
            'nama_institusi' => 'Kampus Uji Coba',
            'secret_key' => 'secret123',
            'status' => true,
        ]);

        $this->user = User::create([
            'name' => 'Admin Tester',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_mahasiswa_index_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('mahasiswas.index'));
        $response->assertStatus(200);
        $response->assertSee('Data Mahasiswa');
    }

    public function test_can_create_new_mahasiswa(): void
    {
        $uniqueNim = 'NIM_' . uniqid();

        $response = $this->actingAs($this->user)->post(route('mahasiswas.store'), [
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Mahasiswa Baru Uji Coba',
        ]);

        $response->assertRedirect(route('mahasiswas.index'));
        $this->assertDatabaseHas('mahasiswas', [
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Mahasiswa Baru Uji Coba',
        ]);
    }

    public function test_rejects_duplicate_nim_in_same_institusi(): void
    {
        $uniqueNim = 'NIM_DUP_' . uniqid();

        Mahasiswa::create([
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Mahasiswa Pertama',
        ]);

        $response = $this->actingAs($this->user)->from(route('mahasiswas.index'))->post(route('mahasiswas.store'), [
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Mahasiswa Duplikat',
        ]);

        $response->assertRedirect(route('mahasiswas.index'));
        $response->assertSessionHasErrors(['nomor_induk_mahasiswa']);
    }

    public function test_can_update_mahasiswa(): void
    {
        $uniqueNim = 'NIM_UPD_' . uniqid();

        $mahasiswa = Mahasiswa::create([
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Nama Sebelum Update',
        ]);

        $response = $this->actingAs($this->user)->put(route('mahasiswas.update', $mahasiswa->id), [
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Nama Setelah Update',
        ]);

        $response->assertRedirect(route('mahasiswas.index'));
        $this->assertDatabaseHas('mahasiswas', [
            'id' => $mahasiswa->id,
            'nama_lengkap' => 'Nama Setelah Update',
        ]);
    }

    public function test_can_delete_mahasiswa(): void
    {
        $uniqueNim = 'NIM_DEL_' . uniqid();

        $mahasiswa = Mahasiswa::create([
            'institusi_id' => $this->institusi->id,
            'nomor_induk_mahasiswa' => $uniqueNim,
            'nama_lengkap' => 'Mahasiswa Dihapus',
        ]);

        $response = $this->actingAs($this->user)->delete(route('mahasiswas.destroy', $mahasiswa->id));
        $response->assertRedirect(route('mahasiswas.index'));
        $this->assertDatabaseMissing('mahasiswas', [
            'id' => $mahasiswa->id,
        ]);
    }
}
