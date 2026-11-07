<?php

namespace App\Http\Controllers;

use App\DataTables\MahasiswaDataTable;
use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\TransaksiPembayaran;
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
        $user = auth()->user();
        if ($user && $user->institusi_id) {
            $institusis = Institusi::where('id', $user->institusi_id)->get();
        } else {
            $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        }

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
        $user = auth()->user();
        if ($user && $user->institusi_id) {
            $institusis = Institusi::where('id', $user->institusi_id)->get();
        } else {
            $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        }
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

        $user = auth()->user();
        if ($user && $user->institusi_id) {
            $validated['institusi_id'] = $user->institusi_id;
        }

        // Pengecekan Composite Unique Constraint ERD: UQ (institusi_id, nomor_induk_mahasiswa)
        $isNimDuplicate = Mahasiswa::where('institusi_id', $validated['institusi_id'])
            ->where('nomor_induk_mahasiswa', $validated['nomor_induk_mahasiswa'])
            ->exists();

        if ($isNimDuplicate) {
            return back()->withInput()->withErrors([
                'nomor_induk_mahasiswa' => 'NIM ' . $validated['nomor_induk_mahasiswa'] . ' sudah terdaftar pada institusi kampus yang dipilih.',
            ]);
        }

        // Jika tidak memilih akun login yang sudah ada, otomatis buatkan akun pengguna baru untuk mahasiswa ini
        if (empty($validated['user_id'])) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $validated['nama_lengkap']));
            $email = $cleanName . '_' . $validated['nomor_induk_mahasiswa'] . '@student.ac.id';

            if (User::where('email', $email)->exists()) {
                $email = 'mhs_' . $validated['nomor_induk_mahasiswa'] . '_' . rand(10, 99) . '@student.ac.id';
            }

            $newUser = User::create([
                'institusi_id' => $validated['institusi_id'],
                'name'         => $validated['nama_lengkap'],
                'email'        => $email,
                'password'     => 'admin123',
                'role'         => 'mahasiswa',
            ]);

            $validated['user_id'] = $newUser->id;
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
        $user = auth()->user();
        if ($user && $user->institusi_id && $mahasiswa->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

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
        $user = auth()->user();
        if ($user && $user->institusi_id && $mahasiswa->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

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
        $user = auth()->user();
        if ($user && $user->institusi_id && $mahasiswa->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

        // Proteksi jika mahasiswa sudah memiliki riwayat tagihan UKT
        if (Schema::hasColumn('tagihan_ukts', 'mahasiswa_id') && $mahasiswa->tagihanUkts()->exists()) {
            Alert::error('Gagal Menghapus', 'Data mahasiswa ini tidak dapat dihapus karena sudah memiliki riwayat Tagihan UKT.');
            return redirect()->route('mahasiswas.index')->with('error', 'Data mahasiswa ini tidak dapat dihapus karena sudah memiliki riwayat Tagihan UKT.');
        }

        $mahasiswa->delete();

        Alert::success('Berhasil', 'Data mahasiswa berhasil dihapus.');
        return redirect()->route('mahasiswas.index')->with('success', 'Data mahasiswa berhasil dihapus.');
    }

    /**
     * Endpoint API (Requirement No. 5):
     * Melihat status tagihan dan riwayat transaksi seorang mahasiswa berdasarkan NIM.
     */
    public function apiGetTagihanDanRiwayat(Request $request, string $nim)
    {
        $query = Mahasiswa::with(['institusi', 'tagihanUkts.periodeAkademik'])
            ->where('nomor_induk_mahasiswa', $nim);

        if ($request->has('kode_institusi')) {
            $query->whereHas('institusi', function ($q) use ($request) {
                $q->where('kode_institusi', $request->query('kode_institusi'));
            });
        }

        $mahasiswa = $query->first();

        if (!$mahasiswa) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Mahasiswa dengan NIM ' . $nim . ' tidak ditemukan.',
            ], 404);
        }

        $tagihanAktif = $mahasiswa->tagihanUkts()
            ->whereIn('status', ['belum_bayar', 'dicicil'])
            ->latest('id')
            ->first();

        $riwayatTransaksi = TransaksiPembayaran::whereHas('tagihanUkt', function ($q) use ($mahasiswa) {
            $q->where('mahasiswa_id', $mahasiswa->id);
        })->latest('id')->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'mahasiswa' => [
                    'nim'            => $mahasiswa->nomor_induk_mahasiswa,
                    'nama'           => $mahasiswa->nama_lengkap,
                    'institusi'      => $mahasiswa->institusi->nama_institusi ?? '-',
                    'kode_institusi' => $mahasiswa->institusi->kode_institusi ?? '-',
                ],
                'tagihan_aktif' => $tagihanAktif ? [
                    'id'            => $tagihanAktif->id,
                    'nomor_tagihan' => $tagihanAktif->nomor_tagihan,
                    'periode'       => ($tagihanAktif->periodeAkademik->tahun_akademik ?? '-') . ' (' . ($tagihanAktif->periodeAkademik->semester ?? '-') . ')',
                    'nominal'       => $tagihanAktif->nominal_tagihan,
                    'total_dibayar' => $tagihanAktif->total_dibayar,
                    'sisa_tagihan'  => $tagihanAktif->sisa_tagihan,
                    'status'        => $tagihanAktif->status,
                    'jatuh_tempo'   => $tagihanAktif->tanggal_jatuh_tempo ? $tagihanAktif->tanggal_jatuh_tempo->format('Y-m-d') : null,
                ] : null,
                'riwayat_tagihan' => $mahasiswa->tagihanUkts->map(fn($t) => [
                    'nomor_tagihan' => $t->nomor_tagihan,
                    'periode'       => ($t->periodeAkademik->tahun_akademik ?? '-') . ' (' . ($t->periodeAkademik->semester ?? '-') . ')',
                    'nominal'       => $t->nominal_tagihan,
                    'total_dibayar' => $t->total_dibayar,
                    'sisa_tagihan'  => $t->sisa_tagihan,
                    'status'        => $t->status,
                ]),
                'riwayat_transaksi' => $riwayatTransaksi->map(fn($trx) => [
                    'gateway_transaction_id' => $trx->gateway_transaction_id,
                    'nominal'                => $trx->nominal_rupiah,
                    'metode'                 => $trx->metode_pembayaran,
                    'status'                 => $trx->status,
                    'dibayar_pada'           => $trx->dibayar_pada ? $trx->dibayar_pada->format('Y-m-d H:i:s') : null,
                ]),
            ],
        ]);
    }
}
