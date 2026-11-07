<?php

namespace App\Http\Controllers;

use App\DataTables\PeriodeAkademikDataTable;
use App\Models\Institusi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use RealRashid\SweetAlert\Facades\Alert;

class PeriodeAkademikController extends Controller
{
    /**
     * Tampilkan daftar periode akademik menggunakan Yajra DataTables & Modal.
     */
    public function index(PeriodeAkademikDataTable $dataTable)
    {
        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        return $dataTable->render('pages.periode_akademik.index', compact('institusis'));
    }

    /**
     * Form tambah periode akademik (halaman mandiri).
     */
    public function create()
    {
        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        return view('pages.periode_akademik.create', compact('institusis'));
    }

    /**
     * Simpan periode akademik baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institusi_id'    => 'required|exists:institusis,id',
            'tahun_akademik'  => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => 'required|in:Ganjil,Genap,Antara',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'institusi_id.required'           => 'Institusi kampus wajib dipilih.',
            'institusi_id.exists'             => 'Institusi kampus tidak valid.',
            'tahun_akademik.required'         => 'Tahun akademik wajib diisi.',
            'tahun_akademik.regex'            => 'Format tahun akademik harus YYYY/YYYY (contoh: 2024/2025).',
            'semester.required'               => 'Semester wajib dipilih.',
            'semester.in'                     => 'Pilihan semester harus Ganjil, Genap, atau Antara.',
            'tanggal_mulai.required'          => 'Tanggal mulai periode wajib diisi.',
            'tanggal_selesai.required'        => 'Tanggal selesai periode wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        // Pengecekan Composite Unique Constraint ERD: (institusi_id, tahun_akademik, semester)
        $isDuplicate = PeriodeAkademik::where('institusi_id', $validated['institusi_id'])
            ->where('tahun_akademik', $validated['tahun_akademik'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($isDuplicate) {
            return back()->withInput()->withErrors([
                'tahun_akademik' => 'Periode akademik untuk tahun dan semester tersebut sudah ada di institusi ini.',
            ]);
        }

        PeriodeAkademik::create($validated);

        Alert::success('Berhasil', 'Periode akademik baru berhasil ditambahkan.');
        return redirect()->route('periode-akademiks.index')->with('success', 'Periode akademik baru berhasil ditambahkan.');
    }

    /**
     * Form edit periode akademik (halaman mandiri).
     */
    public function edit(PeriodeAkademik $periodeAkademik)
    {
        $institusis = Institusi::orderBy('nama_institusi')->get();
        return view('pages.periode_akademik.edit', compact('periodeAkademik', 'institusis'));
    }

    /**
     * Update data periode akademik.
     */
    public function update(Request $request, PeriodeAkademik $periodeAkademik)
    {
        $validated = $request->validate([
            'institusi_id'    => 'required|exists:institusis,id',
            'tahun_akademik'  => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => 'required|in:Ganjil,Genap,Antara',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'institusi_id.required'           => 'Institusi kampus wajib dipilih.',
            'institusi_id.exists'             => 'Institusi kampus tidak valid.',
            'tahun_akademik.required'         => 'Tahun akademik wajib diisi.',
            'tahun_akademik.regex'            => 'Format tahun akademik harus YYYY/YYYY (contoh: 2024/2025).',
            'semester.required'               => 'Semester wajib dipilih.',
            'semester.in'                     => 'Pilihan semester harus Ganjil, Genap, atau Antara.',
            'tanggal_mulai.required'          => 'Tanggal mulai periode wajib diisi.',
            'tanggal_selesai.required'        => 'Tanggal selesai periode wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        // Pengecekan Composite Unique Constraint ERD (abaikan ID saat ini)
        $isDuplicate = PeriodeAkademik::where('institusi_id', $validated['institusi_id'])
            ->where('tahun_akademik', $validated['tahun_akademik'])
            ->where('semester', $validated['semester'])
            ->where('id', '!=', $periodeAkademik->id)
            ->exists();

        if ($isDuplicate) {
            return back()->withInput()->withErrors([
                'tahun_akademik' => 'Periode akademik untuk tahun dan semester tersebut sudah ada di institusi ini.',
            ]);
        }

        $periodeAkademik->update($validated);

        Alert::success('Berhasil', 'Periode akademik berhasil diperbarui.');
        return redirect()->route('periode-akademiks.index')->with('success', 'Periode akademik berhasil diperbarui.');
    }

    /**
     * Hapus periode akademik dengan validasi relasi.
     */
    public function destroy(PeriodeAkademik $periodeAkademik)
    {
        // Proteksi integritas jika sudah memiliki tagihan UKT
        if (Schema::hasColumn('tagihan_ukts', 'periode_akademik_id') && $periodeAkademik->tagihanUkts()->exists()) {
            Alert::error('Gagal Menghapus', 'Periode akademik ini tidak dapat dihapus karena sudah memiliki data Tagihan UKT terkait.');
            return redirect()->route('periode-akademiks.index')->with('error', 'Periode akademik ini tidak dapat dihapus karena sudah memiliki data Tagihan UKT terkait.');
        }

        $periodeAkademik->delete();

        Alert::success('Berhasil', 'Periode akademik berhasil dihapus.');
        return redirect()->route('periode-akademiks.index')->with('success', 'Periode akademik berhasil dihapus.');
    }
}
