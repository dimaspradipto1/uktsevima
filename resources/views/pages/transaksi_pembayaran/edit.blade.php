@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Edit Transaksi Pembayaran</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('transaksi-pembayarans.index') }}">Transaksi Pembayaran</a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Edit Transaksi: {{ $transaksiPembayaran->gateway_transaction_id }}</h5>

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

          <form action="{{ route('transaksi-pembayarans.update', $transaksiPembayaran->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
              <div class="p-3 bg-light rounded small">
                <div>Mahasiswa: <strong>{{ $transaksiPembayaran->tagihanUkt->mahasiswa->nama_lengkap ?? '-' }} ({{ $transaksiPembayaran->tagihanUkt->mahasiswa->nomor_induk_mahasiswa ?? '-' }})</strong></div>
                <div>Institusi: <strong>{{ $transaksiPembayaran->institusi->nama_institusi ?? '-' }}</strong></div>
                <div>No. Tagihan: <span class="font-monospace text-primary">{{ $transaksiPembayaran->tagihanUkt->nomor_tagihan ?? '-' }}</span></div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="nominal_rupiah" class="form-label">Nominal Bayar (Rp) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="nominal_rupiah" id="nominal_rupiah" class="form-control"
                       value="{{ old('nominal_rupiah', $transaksiPembayaran->nominal_rupiah) }}" min="1000" step="1000" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
              <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                <option value="VA" {{ old('metode_pembayaran', $transaksiPembayaran->metode_pembayaran) == 'VA' ? 'selected' : '' }}>Virtual Account (VA)</option>
                <option value="QRIS" {{ old('metode_pembayaran', $transaksiPembayaran->metode_pembayaran) == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                <option value="Transfer" {{ old('metode_pembayaran', $transaksiPembayaran->metode_pembayaran) == 'Transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                <option value="Tunai" {{ old('metode_pembayaran', $transaksiPembayaran->metode_pembayaran) == 'Tunai' ? 'selected' : '' }}>Tunai / Loket Kampus</option>
                <option value="Retail" {{ old('metode_pembayaran', $transaksiPembayaran->metode_pembayaran) == 'Retail' ? 'selected' : '' }}>Retail (Indomaret / Alfamart)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="status" class="form-label">Status Transaksi <span class="text-danger">*</span></label>
              <select name="status" id="status" class="form-select" required>
                <option value="berhasil" {{ old('status', $transaksiPembayaran->status) == 'berhasil' ? 'selected' : '' }}>Berhasil (Dana Diterima)</option>
                <option value="menunggu" {{ old('status', $transaksiPembayaran->status) == 'menunggu' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                <option value="gagal" {{ old('status', $transaksiPembayaran->status) == 'gagal' ? 'selected' : '' }}>Gagal</option>
                <option value="kedaluwarsa" {{ old('status', $transaksiPembayaran->status) == 'kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="dibayar_pada" class="form-label">Waktu Pembayaran</label>
              <input type="datetime-local" name="dibayar_pada" id="dibayar_pada" class="form-control"
                     value="{{ old('dibayar_pada', $transaksiPembayaran->dibayar_pada ? $transaksiPembayaran->dibayar_pada->format('Y-m-d\TH:i') : '') }}">
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('transaksi-pembayarans.index') }}" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
