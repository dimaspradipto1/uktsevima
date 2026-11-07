<?php

namespace App\Http\Controllers;

use App\DataTables\MahasiswaDataTable;
use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class MahasiswaController extends Controller
{
    /**
     * Tampilkan daftar mahasiswa menggunakan Yajra DataTables & Modal.
     */
    public function index(MahasiswaDataTable $dataTable)
    {
        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();

        // Ambil akun pengguna yang belum terhubung dengan profil mahasiswa manapun
        $availableUsers = User::whereDoesntHave('mahasiswa')
            ->orderBy('name')
            ->get();

        return $dataTable->render('pages.mahasiswa.index', compact('institusis', 'availableUsers'));
    }

    /**
     * Form tambah mahasiswa (halaman mandiri).
     */
    public function create()
    {
        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        $availableUsers = User::whereDoesntHave('mahasiswa')->orderBy('name')->get();

        return view('pages.mahasiswa.create', compact('institusis', 'availableUsers'));
    }

    /**
     * Simpan data mahasiswa baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institusi_id'           => 'required|exists:institusis,id',
            'user_id'                => 'nullable|exists:users,id|unique:mahasiswas,user_id',
            'nomor_induk_mahasiswa'  => 'required|string|max:50',
            'nama_lengkap'           => 'required|string|max:150',
        ], [
            'institusi_id.required'          => 'Institusi kampus wajib dipilih.',
            'institusi_id.exists'            => 'Institusi kampus tidak valid.',
            'user_id.unique'                 => 'Akun pengguna ini sudah terhubung dengan data mahasiswa lain.',
            'user_id.exists'                 => 'Akun pengguna yang dipilih tidak ditemukan.',
            'nomor_induk_mahasiswa.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'nomor_induk_mahasiswa.max'      => 'NIM maksimal 50 karakter.',
            'nama_lengkap.required'          => 'Nama lengkap mahasiswa wajib diisi.',
            'nama_lengkap.max'               => 'Nama lengkap maksimal 150 karakter.',
        ]);

        // Pengecekan Composite Unique Constraint ERD: UQ (institusi_id, nomor_induk_mahasiswa)
        $isNimDuplicate = Mahasiswa::where('institusi_id', $validated['institusi_id'])
            ->where('nomor_induk_mahasiswa', $validated['nomor_induk_mahasiswa'])
            ->exists();

        if ($isNimDuplicate) {
            return back()->withInput()->withErrors([
                'nomor_induk_mahasiswa' => 'NIM ' . $validated['nomor_induk_mahasiswa'] . ' sudah terdaftar pada institusi kampus yang dipilih.',
            ]);
        }

        Mahasiswa::create($validated);

        Alert::success('Berhasil', 'Data mahasiswa berhasil ditambahkan.');
        return redirect()->route('mahasiswas.index')->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /**
     * Form edit mahasiswa (halaman mandiri).
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $institusis = Institusi::orderBy('nama_institusi')->get();

        // Pengguna yang belum punya profil mahasiswa + akun pengguna mahasiswa saat ini
        $availableUsers = User::whereDoesntHave('mahasiswa')
            ->orWhere('id', $mahasiswa->user_id)
            ->orderBy('name')
            ->get();

        return view('pages.mahasiswa.edit', compact('mahasiswa', 'institusis', 'availableUsers'));
    }

    /**
     * Update data mahasiswa.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'institusi_id'           => 'required|exists:institusis,id',
            'user_id'                => [
                'nullable',
                'exists:users,id',
                Rule::unique('mahasiswas', 'user_id')->ignore($mahasiswa->id),
            ],
            'nomor_induk_mahasiswa'  => 'required|string|max:50',
            'nama_lengkap'           => 'required|string|max:150',
        ], [
            'institusi_id.required'          => 'Institusi kampus wajib dipilih.',
            'institusi_id.exists'            => 'Institusi kampus tidak valid.',
            'user_id.unique'                 => 'Akun pengguna ini sudah terhubung dengan data mahasiswa lain.',
            'user_id.exists'                 => 'Akun pengguna yang dipilih tidak ditemukan.',
            'nomor_induk_mahasiswa.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'nomor_induk_mahasiswa.max'      => 'NIM maksimal 50 karakter.',
            'nama_lengkap.required'          => 'Nama lengkap mahasiswa wajib diisi.',
            'nama_lengkap.max'               => 'Nama lengkap maksimal 150 karakter.',
        ]);

        // Pengecekan Composite Unique Constraint ERD (abaikan ID saat ini)
        $isNimDuplicate = Mahasiswa::where('institusi_id', $validated['institusi_id'])
            ->where('nomor_induk_mahasiswa', $validated['nomor_induk_mahasiswa'])
            ->where('id', '!=', $mahasiswa->id)
            ->exists();

        if ($isNimDuplicate) {
            return back()->withInput()->withErrors([
                'nomor_induk_mahasiswa' => 'NIM ' . $validated['nomor_induk_mahasiswa'] . ' sudah terdaftar pada institusi kampus yang dipilih.',
            ]);
        }

        $mahasiswa->update($validated);

        Alert::success('Berhasil', 'Data mahasiswa berhasil diperbarui.');
        return redirect()->route('mahasiswas.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Hapus data mahasiswa dengan proteksi integritas relasi.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        // Proteksi jika mahasiswa sudah memiliki riwayat tagihan UKT
        if (Schema::hasColumn('tagihan_ukts', 'mahasiswa_id') && $mahasiswa->tagihanUkts()->exists()) {
            Alert::error('Gagal Menghapus', 'Data mahasiswa ini tidak dapat dihapus karena sudah memiliki riwayat Tagihan UKT.');
            return redirect()->route('mahasiswas.index')->with('error', 'Data mahasiswa ini tidak dapat dihapus karena sudah memiliki riwayat Tagihan UKT.');
        }

        $mahasiswa->delete();

        Alert::success('Berhasil', 'Data mahasiswa berhasil dihapus.');
        return redirect()->route('mahasiswas.index')->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
