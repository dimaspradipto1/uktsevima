@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center">
  <div>
    <h1>Detail Transaksi Pembayaran</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('transaksi-pembayarans.index') }}">Transaksi Pembayaran</a></li>
        <li class="breadcrumb-item active">{{ $transaksiPembayaran->gateway_transaction_id }}</li>
      </ol>
    </nav>
  </div>
  <div>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
      <i class="bi bi-printer me-1"></i> Cetak Bukti Transaksi
    </button>
  </div>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
            <h5 class="card-title p-0 m-0">Kuitansi Pembayaran</h5>
            @if($transaksiPembayaran->status === 'berhasil')
              <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>BERHASIL</span>
            @elseif($transaksiPembayaran->status === 'menunggu')
              <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock me-1"></i>MENUNGGU</span>
            @else
              <span class="badge bg-danger fs-6">{{ strtoupper($transaksiPembayaran->status) }}</span>
            @endif
          </div>

          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">ID Transaksi Gateway:</span>
              <strong class="font-monospace text-primary">{{ $transaksiPembayaran->gateway_transaction_id }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Institusi Kampus:</span>
              <span>{{ $transaksiPembayaran->institusi->nama_institusi ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Mahasiswa:</span>
              <strong>{{ $transaksiPembayaran->tagihanUkt->mahasiswa->nama_lengkap ?? '-' }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">NIM:</span>
              <span class="font-monospace">{{ $transaksiPembayaran->tagihanUkt->mahasiswa->nomor_induk_mahasiswa ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Nomor Tagihan UKT:</span>
              <a href="{{ route('tagihan-ukts.show', $transaksiPembayaran->tagihan_ukt_id) }}" class="font-monospace fw-semibold">
                {{ $transaksiPembayaran->tagihanUkt->nomor_tagihan ?? '-' }}
              </a>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Metode Pembayaran:</span>
              <span class="badge bg-secondary">{{ $transaksiPembayaran->metode_pembayaran }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Waktu Transaksi:</span>
              <span>{{ $transaksiPembayaran->created_at ? $transaksiPembayaran->created_at->format('d M Y H:i:s') : '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Waktu Berhasil Dibayar:</span>
              <span>{{ $transaksiPembayaran->dibayar_pada ? $transaksiPembayaran->dibayar_pada->format('d M Y H:i:s') : '-' }}</span>
            </li>
          </ul>

          <div class="mt-4 p-3 bg-light rounded text-center">
            <div class="small text-muted mb-1">Nominal Pembayaran</div>
            <h3 class="fw-bold text-success m-0">{{ $transaksiPembayaran->format_nominal }}</h3>
          </div>

          <div class="mt-4 text-center">
            <a href="{{ route('transaksi-pembayarans.index') }}" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transaksi
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

