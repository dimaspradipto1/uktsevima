<?php

namespace App\Http\Controllers;

use App\DataTables\LogWebhookDataTable;
use App\Models\Institusi;
use App\Models\LogWebhook;
use App\Models\TagihanUkt;
use App\Models\TransaksiPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class LogWebhookController extends Controller
{
    /**
     * Tampilkan riwayat log webhook menggunakan DataTables.
     */
    public function index(LogWebhookDataTable $dataTable)
    {
        $stats = [
            'total_log'      => LogWebhook::count(),
            'total_valid'    => LogWebhook::where('signature_valid', true)->count(),
            'total_ditolak'  => LogWebhook::whereIn('status_pemprosesan', ['ditolak', 'gagal'])->count(),
            'total_duplikat' => LogWebhook::where('status_pemprosesan', 'duplikat')->count(),
        ];

        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        $transaksis = TransaksiPembayaran::with(['institusi', 'tagihanUkt.mahasiswa'])->latest('id')->limit(50)->get();

        return $dataTable->render('pages.log_webhook.index', compact('stats', 'institusis', 'transaksis'));
    }

    /**
     * Form simulasi pengiriman webhook (halaman mandiri).
     */
    public function create()
    {
        $institusis = Institusi::where('status', true)->orderBy('nama_institusi')->get();
        $transaksis = TransaksiPembayaran::with(['institusi', 'tagihanUkt.mahasiswa'])->latest('id')->limit(50)->get();

        return view('pages.log_webhook.create', compact('institusis', 'transaksis'));
    }

    /**
     * Simpan / Simulasikan Webhook Masuk dari UI Admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institusi_id'            => 'nullable|exists:institusis,id',
            'transaksi_pembayaran_id' => 'nullable|exists:transaksi_pembayarans,id',
            'jenis_event'             => 'required|string|max:50',
            'gateway_event_id'        => 'nullable|string|max:100',
            'simulasi_signature'      => 'required|in:valid,invalid',
        ]);

        $institusi = $validated['institusi_id'] ? Institusi::find($validated['institusi_id']) : null;
        $transaksi = $validated['transaksi_pembayaran_id'] ? TransaksiPembayaran::find($validated['transaksi_pembayaran_id']) : null;

        if ($transaksi && !$institusi) {
            $institusi = $transaksi->institusi;
        }

        $gatewayEventId = (!empty($validated['gateway_event_id'])) 
            ? $validated['gateway_event_id'] 
            : ('EVT-TEST-' . time() . '-' . rand(100, 999));
        $kodeInstitusi = $institusi->kode_institusi ?? 'UNKNOWN';

        // Buat mock payload json
        $payloadData = [
            'event_id'               => $gatewayEventId,
            'event'                  => $validated['jenis_event'],
            'kode_institusi'         => $kodeInstitusi,
            'gateway_transaction_id' => $transaksi?->gateway_transaction_id ?? ('TRX-MOCK-' . rand(1000, 9999)),
            'nominal_bayar'          => $transaksi?->nominal_rupiah ?? 1500000,
            'status'                 => $validated['jenis_event'] === 'payment.success' ? 'berhasil' : 'gagal',
            'timestamp'              => now()->toIso8601String(),
        ];

        $rawPayload = json_encode($payloadData);

        // Hitung HMAC-SHA256 sesuai aturan ERD nomor 5:
        // Signature dihitung dari body mentah (payload_asli) menggunakan secret_key yang terdaftar di institusi
        $secretKey = $institusi?->secret_key ?? 'secret_mock_default';
        if ($validated['simulasi_signature'] === 'valid') {
            $signatureDiterima = hash_hmac('sha256', $rawPayload, $secretKey);
            $signatureValid = true;
        } else {
            $signatureDiterima = 'invalid_signature_hash_' . md5(rand());
            $signatureValid = false;
        }

        // Cek duplikat
        $isDuplikat = LogWebhook::where('gateway_event_id', $gatewayEventId)
            ->where('status_pemprosesan', 'diproses')
            ->exists();

        // Tentukan status pemrosesan
        if ($isDuplikat) {
            $statusPemprosesan = 'duplikat';
            $httpCode = 200;
            $pesan = 'Webhook duplikat diabaikan karena event ID sudah diproses sebelumnya.';
        } elseif (!$signatureValid) {
            $statusPemprosesan = 'ditolak';
            $httpCode = 401;
            $pesan = 'Signature HMAC-SHA256 tidak valid.';
        } elseif (!$institusi) {
            $statusPemprosesan = 'ditolak';
            $httpCode = 404;
            $pesan = 'Institusi kampus tidak dikenali dalam sistem.';
        } else {
            $statusPemprosesan = 'diproses';
            $httpCode = 200;
            $pesan = 'Webhook berhasil diverifikasi dan diproses.';

            // Jika event payment.success dan transaksi ada, update status transaksi & tagihan
            if ($transaksi && $validated['jenis_event'] === 'payment.success') {
                $transaksi->update([
                    'status'       => 'berhasil',
                    'dibayar_pada' => now(),
                ]);

                if ($transaksi->tagihanUkt) {
                    $this->syncTagihanStatus($transaksi->tagihanUkt);
                }
            } elseif ($transaksi && $validated['jenis_event'] === 'payment.expired') {
                $transaksi->update(['status' => 'kedaluwarsa']);
            }
        }

        LogWebhook::create([
            'institusi_id'            => $institusi?->id,
            'transaksi_pembayaran_id' => $transaksi?->id,
            'kode_institusi_diterima' => $kodeInstitusi,
            'gateway_event_id'        => $gatewayEventId,
            'jenis_event'             => $validated['jenis_event'],
            'signature_diterima'      => $signatureDiterima,
            'signature_valid'         => $signatureValid,
            'payload_asli'            => $rawPayload,
            'payload_json'            => $payloadData,
            'status_pemprosesan'      => $statusPemprosesan,
            'kode_respons_http'       => $httpCode,
            'alamat_ip'               => $request->ip() ?? '127.0.0.1',
            'pesan_kesalahan'         => $pesan,
            'diterima_pada'           => now(),
            'diproses_pada'           => now(),
        ]);

        Alert::success('Simulasi Berhasil', 'Log Webhook berhasil dicatat dengan status: ' . strtoupper($statusPemprosesan));
        return redirect()->route('log-webhooks.index')->with('success', 'Log Webhook berhasil dicatat.');
    }

    /**
     * Tampilkan detail rincian Log Webhook (Payload JSON, Signature, dll).
     */
    public function show(LogWebhook $logWebhook)
    {
        $logWebhook->load(['institusi', 'transaksiPembayaran.tagihanUkt.mahasiswa']);
        return view('pages.log_webhook.show', compact('logWebhook'));
    }

    /**
     * Form edit log webhook.
     */
    public function edit(LogWebhook $logWebhook)
    {
        return view('pages.log_webhook.edit', compact('logWebhook'));
    }

    /**
     * Update log webhook.
     */
    public function update(Request $request, LogWebhook $logWebhook)
    {
        $validated = $request->validate([
            'status_pemprosesan' => 'required|in:diterima,diproses,ditolak,gagal,duplikat',
            'kode_respons_http'  => 'required|integer|between:100,599',
            'pesan_kesalahan'    => 'nullable|string',
        ]);

        $logWebhook->update($validated);

        Alert::success('Berhasil', 'Catatan log webhook berhasil diperbarui.');
        return redirect()->route('log-webhooks.index')->with('success', 'Catatan log webhook berhasil diperbarui.');
    }

    /**
     * Hapus log webhook.
     */
    public function destroy(LogWebhook $logWebhook)
    {
        $logWebhook->delete();

        Alert::success('Berhasil', 'Log webhook berhasil dihapus.');
        return redirect()->route('log-webhooks.index')->with('success', 'Log webhook berhasil dihapus.');
    }

    /**
     * Endpoint API Live Webhook Masuk (Dipanggil oleh Payment Gateway).
     * Menerapkan aturan integritas ERD nomor 5 (HMAC-SHA256) dan nomor 7.
     */
    public function handleIncomingWebhook(Request $request)
    {
        $rawPayload = $request->getContent();
        $payloadJson = json_decode($rawPayload, true) ?: $request->all();

        $kodeInstitusi = $request->header('X-Institusi-Code') ?? ($payloadJson['kode_institusi'] ?? null);
        $signatureHeader = $request->header('X-Webhook-Signature') ?? ($payloadJson['signature'] ?? null);
        $gatewayEventId = $request->header('X-Gateway-Event-Id') ?? ($payloadJson['event_id'] ?? ('EVT-' . uniqid()));
        $jenisEvent = $payloadJson['event'] ?? ($payloadJson['jenis_event'] ?? 'payment.success');

        $institusi = $kodeInstitusi ? Institusi::where('kode_institusi', $kodeInstitusi)->first() : null;

        // Verifikasi HMAC-SHA256
        $expectedSignature = null;
        $signatureValid = false;

        if ($institusi && $institusi->secret_key && $signatureHeader) {
            $expectedSignature = hash_hmac('sha256', $rawPayload, $institusi->secret_key);
            $signatureValid = hash_equals($expectedSignature, $signatureHeader);
        }

        // Cek duplikasi
        $isDuplikat = LogWebhook::where('gateway_event_id', $gatewayEventId)
            ->where('status_pemprosesan', 'diproses')
            ->exists();

        $transaksi = null;
        if (!empty($payloadJson['gateway_transaction_id'])) {
            $transaksi = TransaksiPembayaran::where('gateway_transaction_id', $payloadJson['gateway_transaction_id'])->first();
        }

        if ($isDuplikat) {
            $status = 'duplikat';
            $httpCode = 200;
            $pesan = 'Event duplicate. Webhook already processed.';
        } elseif (!$institusi) {
            $status = 'ditolak';
            $httpCode = 404;
            $pesan = 'Institusi not found for code: ' . $kodeInstitusi;
        } elseif (!$signatureValid) {
            $status = 'ditolak';
            $httpCode = 401;
            $pesan = 'Invalid HMAC-SHA256 signature.';
        } else {
            $status = 'diproses';
            $httpCode = 200;
            $pesan = 'Webhook successfully verified and processed.';

            if ($transaksi && $jenisEvent === 'payment.success') {
                $transaksi->update([
                    'status'       => 'berhasil',
                    'dibayar_pada' => now(),
                ]);

                if ($transaksi->tagihanUkt) {
                    $this->syncTagihanStatus($transaksi->tagihanUkt);
                }
            } elseif ($transaksi && $jenisEvent === 'payment.expired') {
                $transaksi->update(['status' => 'kedaluwarsa']);
            }
        }

        LogWebhook::create([
            'institusi_id'            => $institusi?->id,
            'transaksi_pembayaran_id' => $transaksi?->id,
            'kode_institusi_diterima' => $kodeInstitusi,
            'gateway_event_id'        => $gatewayEventId,
            'jenis_event'             => $jenisEvent,
            'signature_diterima'      => $signatureHeader,
            'signature_valid'         => $signatureValid,
            'payload_asli'            => $rawPayload,
            'payload_json'            => $payloadJson,
            'status_pemprosesan'      => $status,
            'kode_respons_http'       => $httpCode,
            'alamat_ip'               => $request->ip(),
            'pesan_kesalahan'         => $pesan,
            'diterima_pada'           => now(),
            'diproses_pada'           => now(),
        ]);

        return response()->json([
            'status'  => $status,
            'message' => $pesan,
        ], $httpCode);
    }

    /**
     * Re-try processing log webhook yang sebelumnya ditolak/gagal.
     */
    public function retryProcessing(LogWebhook $logWebhook)
    {
        $institusi = $logWebhook->institusi;

        if (!$institusi) {
            Alert::error('Gagal', 'Institusi tidak ditemukan, tidak dapat memverifikasi ulang.');
            return back();
        }

        $rawPayload = $logWebhook->payload_asli;
        $expectedSignature = hash_hmac('sha256', $rawPayload, $institusi->secret_key);
        $signatureValid = hash_equals($expectedSignature, $logWebhook->signature_diterima ?? '');

        if ($signatureValid) {
            $logWebhook->update([
                'signature_valid'    => true,
                'status_pemprosesan' => 'diproses',
                'kode_respons_http'  => 200,
                'pesan_kesalahan'    => 'Diproses ulang manual: Signature valid.',
                'diproses_pada'      => now(),
            ]);

            if ($logWebhook->transaksiPembayaran && $logWebhook->jenis_event === 'payment.success') {
                $logWebhook->transaksiPembayaran->update([
                    'status'       => 'berhasil',
                    'dibayar_pada' => now(),
                ]);

                if ($logWebhook->transaksiPembayaran->tagihanUkt) {
                    $this->syncTagihanStatus($logWebhook->transaksiPembayaran->tagihanUkt);
                }
            }

            Alert::success('Berhasil', 'Log Webhook berhasil diproses ulang.');
        } else {
            Alert::error('Gagal', 'Signature tetap tidak valid dengan secret key institusi saat ini.');
        }

        return back();
    }

    /**
     * Sinkronisasi status induk Tagihan UKT.
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
