@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Catat Transaksi Pembayaran</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('transaksi-pembayarans.index') }}">Transaksi Pembayaran</a></li>
      <li class="breadcrumb-item active">Catat</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Form Catat Transaksi Baru</h5>

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

          <form action="{{ route('transaksi-pembayarans.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12">
              <label for="tagihan_ukt_id" class="form-label">Pilih Tagihan UKT Mahasiswa <span class="text-danger">*</span></label>
              <select name="tagihan_ukt_id" id="tagihan_ukt_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Tagihan UKT Mahasiswa --</option>
                @foreach($tagihanUkts as $t)
                  <option value="{{ $t->id }}" {{ old('tagihan_ukt_id') == $t->id ? 'selected' : '' }}>
                    {{ $t->nomor_tagihan }} - {{ $t->mahasiswa->nama_lengkap ?? '-' }} ({{ $t->institusi->nama_institusi ?? '-' }}) [Sisa: {{ $t->format_sisa }}]
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="nominal_rupiah" class="form-label">Nominal Bayar (Rp) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="nominal_rupiah" id="nominal_rupiah" class="form-control" value="{{ old('nominal_rupiah') }}" min="1000" step="1000" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
              <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                <option value="VA" {{ old('metode_pembayaran') == 'VA' ? 'selected' : '' }}>Virtual Account (VA)</option>
                <option value="QRIS" {{ old('metode_pembayaran') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                <option value="Transfer" {{ old('metode_pembayaran') == 'Transfer' ? 'selected' : '' }}>Transfer Bank Manual</option>
                <option value="Tunai" {{ old('metode_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai / Loket Kampus</option>
                <option value="Retail" {{ old('metode_pembayaran') == 'Retail' ? 'selected' : '' }}>Retail (Indomaret / Alfamart)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="status" class="form-label">Status Transaksi <span class="text-danger">*</span></label>
              <select name="status" id="status" class="form-select" required>
                <option value="berhasil" selected>Berhasil (Dana Diterima)</option>
                <option value="menunggu">Menunggu Pembayaran</option>
                <option value="gagal">Gagal</option>
                <option value="kedaluwarsa">Kedaluwarsa</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="dibayar_pada" class="form-label">Waktu Pembayaran</label>
              <input type="datetime-local" name="dibayar_pada" id="dibayar_pada" class="form-control" value="{{ old('dibayar_pada', date('Y-m-d\TH:i')) }}">
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('transaksi-pembayarans.index') }}" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Transaksi
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection

