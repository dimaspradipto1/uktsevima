<?php

namespace App\Http\Controllers;

use App\DataTables\InstitusiDataTable;
use App\Models\Institusi;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class InstitusiController extends Controller
{
    /**
     * Tampilkan daftar data institusi menggunakan Yajra DataTables.
     */
    public function index(InstitusiDataTable $dataTable)
    {
        return $dataTable->render('pages.institusi.index');
    }

    /**
     * Form tambah institusi baru.
     */
    public function create()
    {
        // Generate random 64-character secret key untuk default HMAC
        $defaultSecretKey = bin2hex(random_bytes(32));
        return view('pages.institusi.create', compact('defaultSecretKey'));
    }

    /**
     * Simpan institusi baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_institusi' => 'required|string|max:10|unique:institusis,kode_institusi',
            'nama_institusi' => 'required|string|max:255',
            'secret_key' => 'nullable|string|max:64',
            'status' => 'required|boolean',
        ], [
            'kode_institusi.required' => 'Kode institusi wajib diisi.',
            'kode_institusi.max' => 'Kode institusi maksimal 10 karakter.',
            'kode_institusi.unique' => 'Kode institusi sudah digunakan.',
            'nama_institusi.required' => 'Nama institusi wajib diisi.',
            'nama_institusi.max' => 'Nama institusi maksimal 255 karakter.',
            'secret_key.max' => 'Secret key maksimal 64 karakter.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        // Jika secret key dikosongkan, buat otomatis 64-hex character
        if (empty($validated['secret_key'])) {
            $validated['secret_key'] = bin2hex(random_bytes(32));
        }

        Institusi::create($validated);

        Alert::success('Berhasil', 'Institusi baru berhasil ditambahkan.');
        return redirect()->route('institusis.index')->with('success', 'Institusi baru berhasil ditambahkan.');
    }

    /**
     * Form edit data institusi.
     */
    public function edit(Institusi $institusi)
    {
        return view('pages.institusi.edit', compact('institusi'));
    }

    /**
     * Update data institusi.
     */
    public function update(Request $request, Institusi $institusi)
    {
        $validated = $request->validate([
            'kode_institusi' => 'required|string|max:10|unique:institusis,kode_institusi,' . $institusi->id,
            'nama_institusi' => 'required|string|max:255',
            'secret_key' => 'required|string|max:64',
            'status' => 'required|boolean',
        ], [
            'kode_institusi.required' => 'Kode institusi wajib diisi.',
            'kode_institusi.max' => 'Kode institusi maksimal 10 karakter.',
            'kode_institusi.unique' => 'Kode institusi sudah digunakan institusi lain.',
            'nama_institusi.required' => 'Nama institusi wajib diisi.',
            'nama_institusi.max' => 'Nama institusi maksimal 255 karakter.',
            'secret_key.required' => 'Secret key wajib diisi.',
            'secret_key.max' => 'Secret key maksimal 64 karakter.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $institusi->update($validated);

        Alert::success('Berhasil', 'Data institusi berhasil diperbarui.');
        return redirect()->route('institusis.index')->with('success', 'Data institusi berhasil diperbarui.');
    }

    /**
     * Hapus institusi (dilengkapi pengecekan integritas relasi).
     */
    public function destroy(Institusi $institusi)
    {
        // Cek integritas data relasi sebelum menghapus
        if (
            $institusi->users()->exists() ||
            $institusi->mahasiswas()->exists() ||
            $institusi->periodeAkademiks()->exists() ||
            $institusi->tagihanUkts()->exists() ||
            $institusi->transaksiPembayarans()->exists() ||
            $institusi->logWebhooks()->exists()
        ) {
            Alert::error('Gagal Menghapus', 'Institusi tidak dapat dihapus karena masih memiliki data terkait (Pengguna, Mahasiswa, atau Tagihan).');
            return redirect()->route('institusis.index')->with('error', 'Institusi tidak dapat dihapus karena masih memiliki data terkait.');
        }

        $institusi->delete();

        Alert::success('Berhasil', 'Institusi berhasil dihapus.');
        return redirect()->route('institusis.index')->with('success', 'Institusi berhasil dihapus.');
    }
}
