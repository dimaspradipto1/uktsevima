@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center">
  <div>
    <h1>Detail Log Webhook</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('log-webhooks.index') }}">Log Webhook</a></li>
        <li class="breadcrumb-item active">{{ $logWebhook->gateway_event_id }}</li>
      </ol>
    </nav>
  </div>
  <div>
    @if(!$logWebhook->signature_valid && $logWebhook->institusi)
      <form action="{{ route('log-webhooks.retry', $logWebhook->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-warning btn-sm">
          <i class="bi bi-arrow-repeat me-1"></i> Verifikasi Ulang HMAC
        </button>
      </form>
    @endif
  </div>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Metrik & Parameter Webhook</h5>

          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Event ID Gateway:</span>
              <strong class="font-monospace text-primary">{{ $logWebhook->gateway_event_id ?? '-' }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Jenis Event:</span>
              <span class="badge bg-secondary">{{ $logWebhook->jenis_event }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Institusi Kampus:</span>
              <span>{{ $logWebhook->institusi->nama_institusi ?? $logWebhook->kode_institusi_diterima ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Status Pemrosesan:</span>
              <span class="badge bg-info text-dark">{{ $logWebhook->status_pemprosesan }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Kode Respons HTTP:</span>
              <span class="badge {{ $logWebhook->kode_respons_http == 200 ? 'bg-success' : 'bg-danger' }}">
                {{ $logWebhook->kode_respons_http ?? '-' }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Verifikasi HMAC-SHA256:</span>
              @if($logWebhook->signature_valid)
                <span class="badge bg-success">Valid</span>
              @else
                <span class="badge bg-danger">Invalid / Ditolak</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Alamat IP:</span>
              <span class="font-monospace">{{ $logWebhook->alamat_ip ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Diterima Pada:</span>
              <span>{{ $logWebhook->diterima_pada ? $logWebhook->diterima_pada->format('d M Y H:i:s') : '-' }}</span>
            </li>
          </ul>

          <div class="mt-3">
            <label class="form-label small text-muted">Signature Diterima:</label>
            <div class="font-monospace small bg-light p-2 rounded text-break">
              {{ $logWebhook->signature_diterima ?? 'Tidak ada signature' }}
            </div>
          </div>

          @if($logWebhook->pesan_kesalahan)
            <div class="alert alert-warning py-2 small mt-3">
              <strong>Keterangan:</strong> {{ $logWebhook->pesan_kesalahan }}
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Isi Payload JSON (Raw Webhook Body)</h5>
          <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">{{ json_encode($logWebhook->payload_json, JSON_PRETTY_PRINT) }}</pre>

          <div class="mt-4 text-center">
            <a href="{{ route('log-webhooks.index') }}" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Log
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
