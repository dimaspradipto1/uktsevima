<?php

namespace App\Http\Controllers;

use App\DataTables\TransaksiPembayaranDataTable;
use App\Models\Institusi;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class TransaksiPembayaranController extends Controller
{
    /**
     * Tampilkan data transaksi pembayaran UKT.
     */
    public function index(TransaksiPembayaranDataTable $dataTable)
    {
        $user = auth()->user();
        $trxScope = TransaksiPembayaran::query();
        $tagihanScope = TagihanUkt::query();

        if ($user && $user->role === 'mahasiswa' && $user->mahasiswa) {
            $trxScope->whereHas('tagihanUkt', function ($q) use ($user) {
                $q->where('mahasiswa_id', $user->mahasiswa->id);
            });
            $tagihanScope->where('mahasiswa_id', $user->mahasiswa->id);
            $institusis = Institusi::where('id', $user->mahasiswa->institusi_id)->get();
        } elseif ($user && $user->institusi_id) {
            $trxScope->where('institusi_id', $user->institusi_id);
            $tagihanScope->where('institusi_id', $user->institusi_id);
            $institusis = Institusi::where('id', $user->institusi_id)->get();
        } else {
            $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        }

        $stats = [
            'total_transaksi'        => (clone $trxScope)->count(),
            'total_nominal_berhasil' => (int) (clone $trxScope)->where('status', 'berhasil')->sum('nominal_rupiah'),
            'total_menunggu'         => (clone $trxScope)->where('status', 'menunggu')->count(),
            'total_gagal_expired'    => (clone $trxScope)->whereIn('status', ['gagal', 'kedaluwarsa'])->count(),
        ];

        // Tagihan UKT yang masih aktif untuk opsi tambah pembayaran
        $tagihanUkts = $tagihanScope->with(['institusi', 'mahasiswa', 'periodeAkademik'])
            ->whereIn('status', ['belum_bayar', 'dicicil'])
            ->latest('id')
            ->get();

        return $dataTable->render('pages.transaksi_pembayaran.index', compact('stats', 'institusis', 'tagihanUkts'));
    }

    /**
     * Form tambah transaksi pembayaran (halaman mandiri).
     */
    public function create()
    {
        $user = auth()->user();
        if ($user && $user->institusi_id) {
            $institusis = Institusi::where('id', $user->institusi_id)->get();
            $tagihanUkts = TagihanUkt::with(['institusi', 'mahasiswa', 'periodeAkademik'])
                ->where('institusi_id', $user->institusi_id)
                ->whereIn('status', ['belum_bayar', 'dicicil'])
                ->latest('id')
                ->get();
        } else {
            $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
            $tagihanUkts = TagihanUkt::with(['institusi', 'mahasiswa', 'periodeAkademik'])
                ->whereIn('status', ['belum_bayar', 'dicicil'])
                ->latest('id')
                ->get();
        }

        return view('pages.transaksi_pembayaran.create', compact('institusis', 'tagihanUkts'));
    }

    /**
     * Simpan transaksi pembayaran baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tagihan_ukt_id'    => 'required|exists:tagihan_ukts,id',
            'nominal_rupiah'    => 'required|numeric|min:1000',
            'metode_pembayaran' => 'nullable|string|max:50',
            'status'            => 'required|in:menunggu,berhasil,gagal,kedaluwarsa',
            'dibayar_pada'      => 'nullable|date',
        ], [
            'tagihan_ukt_id.required' => 'Tagihan UKT wajib dipilih.',
            'nominal_rupiah.required' => 'Nominal pembayaran wajib diisi.',
            'nominal_rupiah.min'      => 'Nominal pembayaran minimal Rp 1.000.',
            'status.required'         => 'Status transaksi wajib dipilih.',
            'status.in'               => 'Status transaksi tidak valid.',
        ]);

        $tagihan = TagihanUkt::with('institusi')->findOrFail($validated['tagihan_ukt_id']);

        $user = auth()->user();
        if ($user && $user->institusi_id && $tagihan->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

        // Generate Gateway Transaction ID unik per institusi (ERD UQ)
        $kodeInst = $tagihan->institusi->kode_institusi ?? 'INST';
        $gatewayTrxId = 'TRX-' . $kodeInst . '-' . time() . '-' . rand(100, 999);

        DB::transaction(function () use ($tagihan, $validated, $gatewayTrxId) {
            $dibayarPada = null;
            if ($validated['status'] === 'berhasil') {
                $dibayarPada = $validated['dibayar_pada'] ?? now();
            }

            TransaksiPembayaran::create([
                'institusi_id'           => $tagihan->institusi_id,
                'tagihan_ukt_id'         => $tagihan->id,
                'gateway_transaction_id' => $gatewayTrxId,
                'nominal_rupiah'         => $validated['nominal_rupiah'],
                'metode_pembayaran'      => $validated['metode_pembayaran'] ?? 'VA',
                'status'                 => $validated['status'],
                'dibayar_pada'           => $dibayarPada,
            ]);

            $this->syncTagihanStatus($tagihan);
        });

        Alert::success('Berhasil', 'Transaksi pembayaran berhasil dicatat dengan ID ' . $gatewayTrxId);
        return redirect()->route('transaksi-pembayarans.index')->with('success', 'Transaksi pembayaran berhasil dicatat.');
    }

    /**
     * Tampilkan detail transaksi pembayaran.
     */
    public function show(TransaksiPembayaran $transaksiPembayaran)
    {
        $user = auth()->user();
        if ($user && $user->institusi_id && $transaksiPembayaran->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

        $transaksiPembayaran->load(['institusi', 'tagihanUkt.mahasiswa', 'tagihanUkt.periodeAkademik']);
        return view('pages.transaksi_pembayaran.show', compact('transaksiPembayaran'));
    }

    /**
     * Form edit transaksi pembayaran.
     */
    public function edit(TransaksiPembayaran $transaksiPembayaran)
    {
        $user = auth()->user();
        if ($user && $user->institusi_id && $transaksiPembayaran->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

        $transaksiPembayaran->load(['institusi', 'tagihanUkt.mahasiswa']);
        return view('pages.transaksi_pembayaran.edit', compact('transaksiPembayaran'));
    }

    /**
     * Update data transaksi pembayaran.
     */
    public function update(Request $request, TransaksiPembayaran $transaksiPembayaran)
    {
        $user = auth()->user();
        if ($user && $user->institusi_id && $transaksiPembayaran->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }
        $validated = $request->validate([
            'nominal_rupiah'    => 'required|numeric|min:1000',
            'metode_pembayaran' => 'nullable|string|max:50',
            'status'            => 'required|in:menunggu,berhasil,gagal,kedaluwarsa',
            'dibayar_pada'      => 'nullable|date',
        ], [
            'nominal_rupiah.required' => 'Nominal pembayaran wajib diisi.',
            'nominal_rupiah.min'      => 'Nominal pembayaran minimal Rp 1.000.',
            'status.required'         => 'Status transaksi wajib dipilih.',
            'status.in'               => 'Status transaksi tidak valid.',
        ]);

        DB::transaction(function () use ($transaksiPembayaran, $validated) {
            $dibayarPada = $transaksiPembayaran->dibayar_pada;
            if ($validated['status'] === 'berhasil') {
                $dibayarPada = $validated['dibayar_pada'] ?? ($transaksiPembayaran->dibayar_pada ?? now());
            } elseif ($validated['status'] !== 'berhasil') {
                $dibayarPada = null;
            }

            $transaksiPembayaran->update([
                'nominal_rupiah'    => $validated['nominal_rupiah'],
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status'            => $validated['status'],
                'dibayar_pada'      => $dibayarPada,
            ]);

            if ($transaksiPembayaran->tagihanUkt) {
                $this->syncTagihanStatus($transaksiPembayaran->tagihanUkt);
            }
        });

        Alert::success('Berhasil', 'Data transaksi pembayaran berhasil diperbarui.');
        return redirect()->route('transaksi-pembayarans.index')->with('success', 'Data transaksi pembayaran berhasil diperbarui.');
    }

    /**
     * Hapus transaksi pembayaran.
     */
    public function destroy(TransaksiPembayaran $transaksiPembayaran)
    {
        $user = auth()->user();
        if ($user && $user->institusi_id && $transaksiPembayaran->institusi_id !== $user->institusi_id) {
            abort(403, 'Akses data institusi lain tidak diizinkan.');
        }

        $tagihan = $transaksiPembayaran->tagihanUkt;

        DB::transaction(function () use ($transaksiPembayaran, $tagihan) {
            $transaksiPembayaran->delete();

            if ($tagihan) {
                $this->syncTagihanStatus($tagihan);
            }
        });

        Alert::success('Berhasil', 'Data transaksi pembayaran berhasil dihapus.');
        return redirect()->route('transaksi-pembayarans.index')->with('success', 'Data transaksi pembayaran berhasil dihapus.');
    }

    /**
     * Sinkronisasi status induk Tagihan UKT berdasarkan akumulasi transaksi cicilan yang berhasil
     */
    private function syncTagihanStatus(TagihanUkt $tagihan): void
    {
        $totalBerhasil = (int) $tagihan->transaksiPembayarans()
            ->where('status', 'berhasil')
            ->sum('nominal_rupiah');

        if ($totalBerhasil >= $tagihan->nominal_tagihan) {
            $tagihan->update([
                'status'       => 'lunas',
                'dibayar_pada' => now(),
            ]);
        } elseif ($totalBerhasil > 0) {
            $tagihan->update([
                'status'       => 'dicicil',
                'dibayar_pada' => null,
            ]);
        } else {
            $tagihan->update([
                'status'       => 'belum_bayar',
                'dibayar_pada' => null,
            ]);
        }
    }
}
