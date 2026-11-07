<?php

namespace App\Services;

use App\Models\LogWebhook;
use App\Models\Mahasiswa;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;

class NotificationService
{
    /**
     * Ambil daftar notifikasi riil dan kontekstual berdasarkan peran pengguna yang login
     */
    public static function getNotificationsForUser($user): array
    {
        if (!$user) {
            return [];
        }

        $notifications = [];

        try {
            if ($user->role === 'mahasiswa') {
                $mahasiswa = $user->mahasiswa ?? Mahasiswa::where('user_id', $user->id)->first();
                if ($mahasiswa) {
                    // 1. Tagihan aktif atau belum lunas
                    $tagihanPending = TagihanUkt::where('mahasiswa_id', $mahasiswa->id)
                        ->whereIn('status', ['belum_bayar', 'dicicil'])
                        ->with('periodeAkademik')
                        ->latest('id')
                        ->first();

                    if ($tagihanPending) {
                        $isOverdue = $tagihanPending->tanggal_jatuh_tempo && $tagihanPending->tanggal_jatuh_tempo->isPast();
                        $notifications[] = [
                            'title'   => $isOverdue ? 'Tagihan Lewat Jatuh Tempo' : 'Tagihan UKT Berjalan',
                            'message' => 'Tagihan ' . ($tagihanPending->periodeAkademik->tahun_akademik ?? 'Semester') . ' sisa ' . $tagihanPending->format_sisa . '.',
                            'time'    => $tagihanPending->tanggal_jatuh_tempo ? $tagihanPending->tanggal_jatuh_tempo->diffForHumans() : 'Segera',
                            'icon'    => $isOverdue ? 'bi-exclamation-triangle text-danger' : 'bi-exclamation-circle text-warning',
                            'link'    => route('tagihan-ukts.index'),
                        ];
                    }

                    // 2. Transaksi cicilan/pelunasan berhasil terbaru
                    $trxTerakhir = TransaksiPembayaran::whereHas('tagihanUkt', function ($q) use ($mahasiswa) {
                        $q->where('mahasiswa_id', $mahasiswa->id);
                    })->where('status', 'berhasil')->latest('id')->take(2)->get();

                    foreach ($trxTerakhir as $trx) {
                        $notifications[] = [
                            'title'   => 'Pembayaran Berhasil',
                            'message' => 'Cicilan Rp ' . number_format($trx->nominal_rupiah, 0, ',', '.') . ' via ' . ($trx->metode_pembayaran ?? 'VA') . ' terkonfirmasi.',
                            'time'    => $trx->created_at ? $trx->created_at->diffForHumans() : 'Baru saja',
                            'icon'    => 'bi-check-circle text-success',
                            'link'    => route('tagihan-ukts.index'),
                        ];
                    }
                }
            } elseif ($user->institusi_id) {
                // Finance Admin Kampus
                $institusiId = $user->institusi_id;

                // 1. Transaksi pembayaran terbaru di kampus ini
                $recentTrx = TransaksiPembayaran::where('institusi_id', $institusiId)
                    ->where('status', 'berhasil')
                    ->with('tagihanUkt.mahasiswa')
                    ->latest('id')
                    ->take(3)
                    ->get();

                foreach ($recentTrx as $trx) {
                    $namaMhs = $trx->tagihanUkt->mahasiswa->nama_lengkap ?? 'Mahasiswa';
                    $notifications[] = [
                        'title'   => 'Pembayaran Masuk',
                        'message' => $namaMhs . ' membayar Rp ' . number_format($trx->nominal_rupiah, 0, ',', '.') . ' via ' . ($trx->metode_pembayaran ?? 'VA') . '.',
                        'time'    => $trx->created_at ? $trx->created_at->diffForHumans() : 'Baru saja',
                        'icon'    => 'bi-cash-coin text-success',
                        'link'    => route('transaksi-pembayarans.index'),
                    ];
                }

                // 2. Tagihan melewati batas tanggal jatuh tempo
                $overdueCount = TagihanUkt::where('institusi_id', $institusiId)
                    ->whereIn('status', ['belum_bayar', 'dicicil'])
                    ->where('tanggal_jatuh_tempo', '<', now())
                    ->count();

                if ($overdueCount > 0) {
                    $notifications[] = [
                        'title'   => 'Tagihan Lewat Jatuh Tempo',
                        'message' => "Terdapat {$overdueCount} tagihan mahasiswa yang telah melewati batas tanggal jatuh tempo.",
                        'time'    => 'Perlu Perhatian',
                        'icon'    => 'bi-exclamation-triangle text-danger',
                        'link'    => route('tagihan-ukts.index'),
                    ];
                }
            } else {
                // Super Administrator (Global)
                // 1. Webhook yang memerlukan perhatian (ditolak / duplikat / gagal)
                $badWebhooks = LogWebhook::whereIn('status_pemprosesan', ['ditolak', 'duplikat', 'gagal'])
                    ->latest('id')
                    ->take(2)
                    ->get();

                foreach ($badWebhooks as $webhook) {
                    $notifications[] = [
                        'title'   => 'Log Webhook ' . ucfirst($webhook->status_pemprosesan),
                        'message' => 'Event ' . ($webhook->gateway_event_id ?? 'ID') . ' status ' . $webhook->status_pemprosesan . ' (HTTP ' . $webhook->kode_respons_http . ').',
                        'time'    => $webhook->created_at ? $webhook->created_at->diffForHumans() : 'Baru saja',
                        'icon'    => 'bi-shield-exclamation text-danger',
                        'link'    => route('log-webhooks.index'),
                    ];
                }

                // 2. Transaksi pembayaran terbaru lintas kampus
                $recentGlobalTrx = TransaksiPembayaran::where('status', 'berhasil')
                    ->with(['institusi', 'tagihanUkt.mahasiswa'])
                    ->latest('id')
                    ->take(3)
                    ->get();

                foreach ($recentGlobalTrx as $trx) {
                    $instNama = $trx->institusi->nama_institusi ?? 'Kampus';
                    $notifications[] = [
                        'title'   => 'Pembayaran UKT Berhasil',
                        'message' => 'Rp ' . number_format($trx->nominal_rupiah, 0, ',', '.') . ' di ' . $instNama . '.',
                        'time'    => $trx->created_at ? $trx->created_at->diffForHumans() : 'Baru saja',
                        'icon'    => 'bi-check-circle text-success',
                        'link'    => route('transaksi-pembayarans.index'),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Fallback graceful jika database sedang dimigrasi / tabel belum siap
            return [];
        }

        return $notifications;
    }
}
