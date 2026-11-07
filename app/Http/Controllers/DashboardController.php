<?php

namespace App\Http\Controllers;

use App\Models\Institusi;
use App\Models\LogWebhook;
use App\Models\Mahasiswa;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Berdasarkan Peran Pengguna (Super Admin, Admin Kampus, Mahasiswa)
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'mahasiswa') {
            return $this->dashboardMahasiswa($user);
        }

        if ($user->institusi_id) {
            return $this->dashboardFinanceAdmin($user);
        }

        return $this->dashboardSuperAdmin($user);
    }

    /**
     * Dashboard untuk Super Administrator (Global Lintas Kampus)
     */
    protected function dashboardSuperAdmin($user)
    {
        $title = 'Dashboard Super Administrator';
        $roleView = 'super_admin';

        // 1. Metrik Utama Global
        $totalInstitusi     = Institusi::count();
        $totalMahasiswa     = Mahasiswa::count();
        $totalTagihanGlobal = (int) TagihanUkt::sum('nominal_tagihan');
        $totalDanaMasuk     = (int) TransaksiPembayaran::where('status', 'berhasil')->sum('nominal_rupiah');
        $totalPiutangGlobal = max(0, $totalTagihanGlobal - $totalDanaMasuk);
        $globalPelunasanRate = $totalTagihanGlobal > 0 ? round(($totalDanaMasuk / $totalTagihanGlobal) * 100) : 0;

        // 2. Status Distribusi Tagihan
        $statusCounts = [
            'lunas'       => TagihanUkt::where('status', 'lunas')->count(),
            'dicicil'     => TagihanUkt::where('status', 'dicicil')->count(),
            'belum_bayar' => TagihanUkt::where('status', 'belum_bayar')->count(),
            'kadaluarsa'  => TagihanUkt::where('status', 'kadaluarsa')->count(),
        ];
        $totalTagihanCount = array_sum($statusCounts);

        // 3. Statistik Log Webhook
        $webhookStats = [
            'total'     => LogWebhook::count(),
            'diproses'  => LogWebhook::where('status_pemprosesan', 'diproses')->count(),
            'ditolak'   => LogWebhook::where('status_pemprosesan', 'ditolak')->count(),
            'duplikat'  => LogWebhook::where('status_pemprosesan', 'duplikat')->count(),
            'gagal'     => LogWebhook::where('status_pemprosesan', 'gagal')->count(),
        ];

        // 4. Ringkasan Performa Per Kampus
        $institusis = Institusi::withCount('mahasiswas')
            ->withSum('tagihanUkts as total_tagihan', 'nominal_tagihan')
            ->get();

        $institusiSummary = $institusis->map(function ($inst) {
            $danaMasuk = (int) TransaksiPembayaran::where('institusi_id', $inst->id)
                ->where('status', 'berhasil')
                ->sum('nominal_rupiah');
            $tagihan = (int) ($inst->total_tagihan ?? 0);
            $rate = $tagihan > 0 ? round(($danaMasuk / $tagihan) * 100) : 0;

            return [
                'id'              => $inst->id,
                'nama'            => $inst->nama_institusi,
                'kode'            => $inst->kode_institusi,
                'mahasiswa_count' => $inst->mahasiswas_count,
                'total_tagihan'   => $tagihan,
                'dana_masuk'      => $danaMasuk,
                'rate'            => $rate,
            ];
        });

        // 5. Transaksi Terbaru Lintas Kampus (5 Teratas)
        $transaksiTerbaru = TransaksiPembayaran::with(['institusi', 'tagihanUkt.mahasiswa', 'tagihanUkt.periodeAkademik'])
            ->latest('id')
            ->take(6)
            ->get();

        // 6. Log Webhook Terbaru
        $logWebhooks = LogWebhook::with('institusi')
            ->latest('id')
            ->take(6)
            ->get();

        // 7. Data Tren Transaksi 7 Hari Terakhir
        $chartDates = [];
        $chartNominals = [];
        $chartCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $carbon = Carbon::now()->subDays($i);
            $chartDates[] = $carbon->format('d M');
            $dateStr = $carbon->format('Y-m-d');

            $dayTrx = TransaksiPembayaran::whereDate('created_at', $dateStr)
                ->where('status', 'berhasil');

            $chartNominals[] = (int) $dayTrx->sum('nominal_rupiah');
            $chartCounts[] = (int) $dayTrx->count();
        }

        return view('layouts.dashboard.index', compact(
            'title',
            'roleView',
            'totalInstitusi',
            'totalMahasiswa',
            'totalTagihanGlobal',
            'totalDanaMasuk',
            'totalPiutangGlobal',
            'globalPelunasanRate',
            'statusCounts',
            'totalTagihanCount',
            'webhookStats',
            'institusiSummary',
            'transaksiTerbaru',
            'logWebhooks',
            'chartDates',
            'chartNominals',
            'chartCounts'
        ));
    }

    /**
     * Dashboard untuk Finance Admin Kampus (Terisolasi per Institusi)
     */
    protected function dashboardFinanceAdmin($user)
    {
        $institusiId = $user->institusi_id;
        $institusi = $user->institusi;
        $title = 'Dashboard Keuangan - ' . ($institusi ? $institusi->nama_institusi : 'Kampus');
        $roleView = 'finance_admin';

        // 1. Metrik Keuangan Kampus
        $totalMahasiswa     = Mahasiswa::where('institusi_id', $institusiId)->count();
        $totalTagihanNominal = (int) TagihanUkt::where('institusi_id', $institusiId)->sum('nominal_tagihan');
        $totalDanaTerkumpul = (int) TransaksiPembayaran::where('institusi_id', $institusiId)
            ->where('status', 'berhasil')
            ->sum('nominal_rupiah');
        $totalPiutang       = max(0, $totalTagihanNominal - $totalDanaTerkumpul);
        $pelunasanRate      = $totalTagihanNominal > 0 ? round(($totalDanaTerkumpul / $totalTagihanNominal) * 100) : 0;

        // 2. Status Distribusi Tagihan Kampus
        $statusCounts = [
            'lunas'       => TagihanUkt::where('institusi_id', $institusiId)->where('status', 'lunas')->count(),
            'dicicil'     => TagihanUkt::where('institusi_id', $institusiId)->where('status', 'dicicil')->count(),
            'belum_bayar' => TagihanUkt::where('institusi_id', $institusiId)->where('status', 'belum_bayar')->count(),
            'kadaluarsa'  => TagihanUkt::where('institusi_id', $institusiId)->where('status', 'kadaluarsa')->count(),
        ];
        $totalTagihanCount = array_sum($statusCounts);

        // 3. Transaksi Masuk Terbaru di Kampus Ini
        $transaksiTerbaru = TransaksiPembayaran::where('institusi_id', $institusiId)
            ->with(['tagihanUkt.mahasiswa', 'tagihanUkt.periodeAkademik'])
            ->latest('id')
            ->take(6)
            ->get();

        // 4. Tagihan Mendekati / Lewat Jatuh Tempo
        $tagihanJatuhTempo = TagihanUkt::where('institusi_id', $institusiId)
            ->whereIn('status', ['belum_bayar', 'dicicil'])
            ->with(['mahasiswa', 'periodeAkademik'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->take(5)
            ->get();

        // 5. Tren Penerimaan Transaksi Kampus 7 Hari Terakhir
        $chartDates = [];
        $chartNominals = [];
        $chartCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $carbon = Carbon::now()->subDays($i);
            $chartDates[] = $carbon->format('d M');
            $dateStr = $carbon->format('Y-m-d');

            $dayTrx = TransaksiPembayaran::where('institusi_id', $institusiId)
                ->whereDate('created_at', $dateStr)
                ->where('status', 'berhasil');

            $chartNominals[] = (int) $dayTrx->sum('nominal_rupiah');
            $chartCounts[] = (int) $dayTrx->count();
        }

        return view('layouts.dashboard.index', compact(
            'title',
            'roleView',
            'institusi',
            'totalMahasiswa',
            'totalTagihanNominal',
            'totalDanaTerkumpul',
            'totalPiutang',
            'pelunasanRate',
            'statusCounts',
            'totalTagihanCount',
            'transaksiTerbaru',
            'tagihanJatuhTempo',
            'chartDates',
            'chartNominals',
            'chartCounts'
        ));
    }

    /**
     * Dashboard untuk Mahasiswa
     */
    protected function dashboardMahasiswa($user)
    {
        $title = 'Portal Tagihan & Pembayaran Mahasiswa';
        $roleView = 'mahasiswa';

        $mahasiswa = $user->mahasiswa()->with('institusi')->first();

        // Fallback jika belum ditautkan
        if (!$mahasiswa) {
            $mahasiswa = Mahasiswa::with('institusi')->where('user_id', $user->id)->first();
        }

        $tagihans = collect();
        $tagihanAktif = null;
        $totalTagihan = 0;
        $totalTerbayar = 0;
        $sisaTagihan = 0;
        $transaksiTerbaru = collect();
        $lunasCount = 0;
        $belumLunasCount = 0;

        if ($mahasiswa) {
            $tagihans = TagihanUkt::where('mahasiswa_id', $mahasiswa->id)
                ->with(['periodeAkademik', 'institusi', 'transaksiPembayarans' => fn($q) => $q->latest('id')])
                ->orderBy('id', 'desc')
                ->get();

            // Tagihan semester aktif: prioritas belum lunas/dicicil, fallback tagihan terbaru
            $tagihanAktif = $tagihans->first(fn($t) => in_array($t->status, ['belum_bayar', 'dicicil'])) ?? $tagihans->first();

            $totalTagihan = (int) $tagihans->sum('nominal_tagihan');
            $tagihanIds = $tagihans->pluck('id');

            $totalTerbayar = (int) TransaksiPembayaran::whereIn('tagihan_ukt_id', $tagihanIds)
                ->where('status', 'berhasil')
                ->sum('nominal_rupiah');

            $sisaTagihan = max(0, $totalTagihan - $totalTerbayar);

            $lunasCount = $tagihans->where('status', 'lunas')->count();
            $belumLunasCount = $tagihans->count() - $lunasCount;

            $transaksiTerbaru = TransaksiPembayaran::whereIn('tagihan_ukt_id', $tagihanIds)
                ->with('tagihanUkt.periodeAkademik')
                ->latest('id')
                ->take(5)
                ->get();
        }

        return view('layouts.dashboard.index', compact(
            'title',
            'roleView',
            'mahasiswa',
            'tagihanAktif',
            'tagihans',
            'totalTagihan',
            'totalTerbayar',
            'sisaTagihan',
            'lunasCount',
            'belumLunasCount',
            'transaksiTerbaru'
        ));
    }
}
