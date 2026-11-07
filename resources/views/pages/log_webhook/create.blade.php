@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Uji Coba Simulasi Webhook</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('log-webhooks.index') }}">Log Webhook</a></li>
      <li class="breadcrumb-item active">Simulasi</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Form Simulasi Webhook Masuk (HMAC-SHA256)</h5>

          @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong>Terjadi Kesalahan:</strong>
              <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <form action="{{ route('log-webhooks.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-md-6">
              <label for="institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
              <select name="institusi_id" id="institusi_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}">{{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="transaksi_pembayaran_id" class="form-label">Tautkan Transaksi Pembayaran</label>
              <select name="transaksi_pembayaran_id" id="transaksi_pembayaran_id" class="form-select">
                <option value="">-- Simulasi Transaksi Bebas --</option>
                @foreach($transaksis as $trx)
                  <option value="{{ $trx->id }}">
                    {{ $trx->gateway_transaction_id }} - {{ $trx->tagihanUkt->mahasiswa->nama_lengkap ?? '-' }} [{{ $trx->format_nominal }}]
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="jenis_event" class="form-label">Jenis Event Gateway <span class="text-danger">*</span></label>
              <select name="jenis_event" id="jenis_event" class="form-select" required>
                <option value="payment.success" selected>payment.success (Pembayaran Berhasil)</option>
                <option value="payment.expired">payment.expired (Batas Waktu Habis)</option>
                <option value="payment.failed">payment.failed (Pembayaran Gagal)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="simulasi_signature" class="form-label">Skenario Signature HMAC <span class="text-danger">*</span></label>
              <select name="simulasi_signature" id="simulasi_signature" class="form-select" required>
                <option value="valid" selected>Valid (HMAC Cocok dengan Secret Key Kampus)</option>
                <option value="invalid">Invalid (Simulasi Signature Palsu &rarr; HTTP 401)</option>
              </select>
            </div>

            <div class="col-12">
              <label for="gateway_event_id" class="form-label">Event ID Gateway (Opsional)</label>
              <input type="text" name="gateway_event_id" id="gateway_event_id" class="form-control font-monospace" placeholder="Kosongkan untuk auto-generate">
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('log-webhooks.index') }}" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-check me-1"></i> Kirim Simulasi Webhook
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection

