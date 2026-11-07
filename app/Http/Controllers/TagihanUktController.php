<?php

namespace App\Http\Controllers;

use App\DataTables\TagihanUktDataTable;
use App\Models\Institusi;
use App\Models\Mahasiswa;
use App\Models\PeriodeAkademik;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TagihanUktController extends Controller
{
    /**
     * Tampilkan data tagihan UKT.
     * Jika role adalah 'mahasiswa', tampilkan Portal Tagihan & Cicilan Mahasiswa.
     * Jika role adalah admin / finance, tampilkan Yajra DataTables manajemen master.
     */
    public function index(TagihanUktDataTable $dataTable)
    {
        $user = auth()->user();

        // 1. Tampilan Khusus Mahasiswa
        if ($user && $user->role === 'mahasiswa') {
            $mahasiswa = $user->mahasiswa;

            $tagihanAktif = null;
            $riwayatTagihan = collect();

            if ($mahasiswa) {
                // Tagihan aktif yang belum lunas
                $tagihanAktif = TagihanUkt::where('mahasiswa_id', $mahasiswa->id)
                    ->whereIn('status', ['belum_bayar', 'dicicil'])
                    ->with(['institusi', 'periodeAkademik', 'transaksiPembayarans' => fn($q) => $q->latest()])
                    ->latest('id')
                    ->first();

                // Seluruh riwayat tagihan mahasiswa
                $riwayatTagihan = TagihanUkt::where('mahasiswa_id', $mahasiswa->id)
                    ->with(['institusi', 'periodeAkademik', 'transaksiPembayarans' => fn($q) => $q->latest()])
                    ->latest('id')
                    ->get();
            }

            return view('pages.tagihan_ukt.mahasiswa', compact('mahasiswa', 'tagihanAktif', 'riwayatTagihan'));
        }

        // 2. Tampilan Admin / Finance
        $stats = [
            'total_tagihan'     => TagihanUkt::count(),
            'total_nominal'     => (int) TagihanUkt::sum('nominal_tagihan'),
            'total_terbayar'    => (int) TransaksiPembayaran::where('status', 'berhasil')->sum('nominal_bayar'),
            'total_lunas'       => TagihanUkt::where('status', 'lunas')->count(),
            'total_belum_lunas' => TagihanUkt::whereIn('status', ['belum_bayar', 'dicicil'])->count(),
        ];
        $stats['sisa_piutang'] = max(0, $stats['total_nominal'] - $stats['total_terbayar']);

        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        $periodes = PeriodeAkademik::with('institusi')->latest('id')->get();
        $mahasiswas = Mahasiswa::with('institusi')->orderBy('nama_lengkap')->get();

        return $dataTable->render('pages.tagihan_ukt.index', compact('stats', 'institusis', 'periodes', 'mahasiswas'));
    }

    /**
     * Form tambah tagihan UKT (halaman mandiri).
     */
    public function create()
    {
        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        $periodes = PeriodeAkademik::with('institusi')->latest('id')->get();
        $mahasiswas = Mahasiswa::with('institusi')->orderBy('nama_lengkap')->get();

        return view('pages.tagihan_ukt.create', compact('institusis', 'periodes', 'mahasiswas'));
    }

    /**
     * Simpan tagihan UKT baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institusi_id'        => 'required|exists:institusis,id',
            'mahasiswa_id'        => 'required|exists:mahasiswas,id',
            'periode_akademik_id' => 'required|exists:periode_akademiks,id',
            'nominal_tagihan'     => 'required|numeric|min:10000',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
        ], [
            'institusi_id.required'        => 'Institusi kampus wajib dipilih.',
            'mahasiswa_id.required'        => 'Mahasiswa wajib dipilih.',
            'periode_akademik_id.required' => 'Periode semester wajib dipilih.',
            'nominal_tagihan.required'     => 'Nominal tagihan UKT wajib diisi.',
            'nominal_tagihan.min'          => 'Nominal tagihan minimal Rp 10.000.',
            'tanggal_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tanggal_jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh lewat dari hari ini.',
        ]);

        // Pengecekan Composite Unique ERD: UQ (institusi_id, mahasiswa_id, periode_akademik_id)
        $isDuplicate = TagihanUkt::where('institusi_id', $validated['institusi_id'])
            ->where('mahasiswa_id', $validated['mahasiswa_id'])
            ->where('periode_akademik_id', $validated['periode_akademik_id'])
            ->exists();

        if ($isDuplicate) {
            return back()->withInput()->withErrors([
                'mahasiswa_id' => 'Mahasiswa tersebut sudah memiliki Tagihan UKT pada periode akademik yang dipilih.',
            ]);
        }

        // Auto-generate Nomor Tagihan: INV/{KODE_INST}/{TAHUN}/{SEQUENCE}
        $institusi = Institusi::findOrFail($validated['institusi_id']);
        $prefix = 'INV-' . $institusi->kode_institusi . '-' . date('Y');
        $countThisYear = TagihanUkt::where('institusi_id', $institusi->id)
            ->whereYear('created_at', date('Y'))
            ->count() + 1;
        $nomorTagihan = $prefix . '-' . str_pad($countThisYear, 5, '0', STR_PAD_LEFT);

        TagihanUkt::create([
            'institusi_id'        => $validated['institusi_id'],
            'mahasiswa_id'        => $validated['mahasiswa_id'],
            'periode_akademik_id' => $validated['periode_akademik_id'],
            'dibuat_oleh'         => auth()->id(),
            'nomor_tagihan'       => $nomorTagihan,
            'nominal_tagihan'     => $validated['nominal_tagihan'],
            'status'              => 'belum_bayar',
            'diterbitkan_pada'    => now(),
            'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
            'dibayar_pada'        => null,
        ]);

        Alert::success('Berhasil', 'Tagihan UKT baru dengan nomor ' . $nomorTagihan . ' berhasil diterbitkan.');
        return redirect()->route('tagihan-ukts.index')->with('success', 'Tagihan UKT berhasil diterbitkan.');
    }

    /**
     * Tampilkan detail tagihan dan histori pembayaran / cicilan bulanan.
     */
    public function show(TagihanUkt $tagihanUkt)
    {
        $tagihanUkt->load(['institusi', 'mahasiswa', 'periodeAkademik', 'pembuat', 'transaksiPembayarans' => fn($q) => $q->latest('id')]);
        return view('pages.tagihan_ukt.show', compact('tagihanUkt'));
    }

    /**
     * Form edit tagihan UKT.
     */
    public function edit(TagihanUkt $tagihanUkt)
    {
        if ($tagihanUkt->status === 'lunas') {
            Alert::warning('Perhatian', 'Tagihan yang sudah lunas tidak dapat diubah.');
            return redirect()->route('tagihan-ukts.index');
        }

        $institusis = Institusi::orderBy('nama_institusi')->get();
        $periodes = PeriodeAkademik::where('institusi_id', $tagihanUkt->institusi_id)->get();
        $mahasiswas = Mahasiswa::where('institusi_id', $tagihanUkt->institusi_id)->get();

        return view('pages.tagihan_ukt.edit', compact('tagihanUkt', 'institusis', 'periodes', 'mahasiswas'));
    }

    /**
     * Update tagihan UKT.
     */
    public function update(Request $request, TagihanUkt $tagihanUkt)
    {
        if ($tagihanUkt->status === 'lunas') {
            Alert::error('Gagal', 'Tagihan yang sudah lunas tidak dapat diubah.');
            return redirect()->route('tagihan-ukts.index');
        }

        $validated = $request->validate([
            'nominal_tagihan'     => 'required|numeric|min:' . $tagihanUkt->total_dibayar,
            'tanggal_jatuh_tempo' => 'required|date',
        ], [
            'nominal_tagihan.min' => 'Nominal tagihan tidak boleh lebih kecil dari jumlah cicilan yang sudah dibayarkan (Rp ' . number_format($tagihanUkt->total_dibayar, 0, ',', '.') . ').',
        ]);

        // Cek status baru berdasarkan nominal baru vs total yang sudah dibayar
        $status = $tagihanUkt->status;
        $dibayarPada = $tagihanUkt->dibayar_pada;

        if ($tagihanUkt->total_dibayar >= $validated['nominal_tagihan']) {
            $status = 'lunas';
            $dibayarPada = now();
        } elseif ($tagihanUkt->total_dibayar > 0) {
            $status = 'dicicil';
        }

        $tagihanUkt->update([
            'nominal_tagihan'     => $validated['nominal_tagihan'],
            'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
            'status'              => $status,
            'dibayar_pada'        => $dibayarPada,
        ]);

        Alert::success('Berhasil', 'Data tagihan UKT berhasil diperbarui.');
        return redirect()->route('tagihan-ukts.index')->with('success', 'Data tagihan UKT berhasil diperbarui.');
    }

    /**
     * Hapus tagihan UKT (hanya jika belum ada cicilan yang masuk).
     */
    public function destroy(TagihanUkt $tagihanUkt)
    {
        if ($tagihanUkt->total_dibayar > 0) {
            Alert::error('Gagal Menghapus', 'Tagihan ini tidak dapat dihapus karena sudah memiliki catatan cicilan/transaksi pembayaran.');
            return redirect()->route('tagihan-ukts.index')->with('error', 'Tagihan ini tidak dapat dihapus karena sudah memiliki catatan cicilan pembayaran.');
        }

        $tagihanUkt->delete();

        Alert::success('Berhasil', 'Tagihan UKT berhasil dihapus.');
        return redirect()->route('tagihan-ukts.index')->with('success', 'Tagihan UKT berhasil dihapus.');
    }

    /**
     * Bayar Cicilan Bulanan / Pelunasan UKT (Model 1).
     */
    public function bayarCicilan(Request $request, TagihanUkt $tagihanUkt)
    {
        $sisa = $tagihanUkt->sisa_tagihan;

        if ($sisa <= 0 || $tagihanUkt->status === 'lunas') {
            Alert::info('Informasi', 'Tagihan UKT ini sudah lunas sepenuhnya.');
            return back();
        }

        $validated = $request->validate([
            'nominal_bayar'     => ['required', 'numeric', 'min:10000', 'max:' . $sisa],
            'metode_pembayaran' => 'required|string|in:VA,QRIS,Retail,Transfer,Tunai',
        ], [
            'nominal_bayar.required' => 'Nominal cicilan yang dibayarkan wajib diisi.',
            'nominal_bayar.min'      => 'Nominal cicilan minimal Rp 10.000.',
            'nominal_bayar.max'      => 'Nominal cicilan tidak boleh melebihi sisa tagihan (Rp ' . number_format($sisa, 0, ',', '.') . ').',
            'metode_pembayaran.in'   => 'Pilihan metode pembayaran tidak valid.',
        ]);

        DB::transaction(function () use ($tagihanUkt, $validated) {
            $gatewayTrxId = 'TRX-' . $tagihanUkt->institusi_id . '-' . time() . '-' . rand(100, 999);

            // 1. Simpan Transaksi Pembayaran Cicilan
            TransaksiPembayaran::create([
                'institusi_id'           => $tagihanUkt->institusi_id,
                'tagihan_ukt_id'         => $tagihanUkt->id,
                'gateway_transaction_id' => $gatewayTrxId,
                'nominal_bayar'          => $validated['nominal_bayar'],
                'metode_pembayaran'      => $validated['metode_pembayaran'],
                'status'                 => 'berhasil',
                'dibayar_pada'           => now(),
            ]);

            // 2. Evaluasi Status Induk Tagihan UKT
            $totalBaru = $tagihanUkt->total_dibayar + $validated['nominal_bayar'];

            if ($totalBaru >= $tagihanUkt->nominal_tagihan) {
                $tagihanUkt->update([
                    'status'       => 'lunas',
                    'dibayar_pada' => now(),
                ]);
            } else {
                $tagihanUkt->update([
                    'status' => 'dicicil',
                ]);
            }
        });

        Alert::success('Pembayaran Berhasil', 'Pembayaran cicilan sebesar Rp ' . number_format($validated['nominal_bayar'], 0, ',', '.') . ' berhasil dicatat.');
        return back()->with('success', 'Pembayaran cicilan berhasil disimpan.');
    }
}
