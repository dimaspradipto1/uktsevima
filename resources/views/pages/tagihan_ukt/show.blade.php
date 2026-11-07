@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center">
  <div>
    <h1>Detail Tagihan UKT</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('tagihan-ukts.index') }}">Tagihan UKT</a></li>
        <li class="breadcrumb-item active">{{ $tagihanUkt->nomor_tagihan }}</li>
      </ol>
    </nav>
  </div>
  <div>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
      <i class="bi bi-printer me-1"></i> Cetak Kwitansi
    </button>
  </div>
</div><!-- End Page Title -->

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<section class="section">
  <div class="row">
    <!-- Kolom Ringkasan Invoice Tagihan -->
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
            <h5 class="card-title p-0 m-0">Informasi Tagihan</h5>
            @if($tagihanUkt->status === 'lunas')
              <span class="badge bg-success fs-6">LUNAS</span>
            @elseif($tagihanUkt->status === 'dicicil')
              <span class="badge bg-info text-dark fs-6">DICICIL</span>
            @else
              <span class="badge bg-warning text-dark fs-6">BELUM BAYAR</span>
            @endif
          </div>

          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Nomor Tagihan:</span>
              <strong class="font-monospace text-primary">{{ $tagihanUkt->nomor_tagihan }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Institusi Kampus:</span>
              <span>{{ $tagihanUkt->institusi->nama_institusi ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Mahasiswa:</span>
              <strong>{{ $tagihanUkt->mahasiswa->nama_lengkap ?? '-' }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">NIM:</span>
              <span class="font-monospace">{{ $tagihanUkt->mahasiswa->nomor_induk_mahasiswa ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Periode Semester:</span>
              <span>{{ $tagihanUkt->periodeAkademik->tahun_akademik ?? '-' }} - {{ $tagihanUkt->periodeAkademik->semester ?? '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Diterbitkan Pada:</span>
              <span>{{ $tagihanUkt->diterbitkan_pada ? $tagihanUkt->diterbitkan_pada->format('d M Y H:i') : '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Batas Jatuh Tempo:</span>
              <span>{{ $tagihanUkt->tanggal_jatuh_tempo ? $tagihanUkt->tanggal_jatuh_tempo->format('d M Y') : '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Dibuat Oleh:</span>
              <span>{{ $tagihanUkt->pembuat->name ?? 'Sistem' }}</span>
            </li>
          </ul>

          <div class="mt-4 p-3 bg-light rounded text-center">
            <div class="small text-muted mb-1">Total Tagihan UKT</div>
            <h4 class="fw-bold text-dark m-0">{{ $tagihanUkt->format_nominal }}</h4>
            <hr class="my-2">
            <div class="d-flex justify-content-between small text-muted">
              <span>Sudah Terbayar:</span>
              <strong class="text-success">{{ $tagihanUkt->format_dibayar }}</strong>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-1">
              <span>Sisa Pembayaran:</span>
              <strong class="text-danger">{{ $tagihanUkt->format_sisa }}</strong>
            </div>
          </div>

          @if($tagihanUkt->sisa_tagihan > 0)
            <button type="button" class="btn btn-success w-100 mt-3" data-bs-toggle="modal" data-bs-target="#modalCatatCicilan">
              <i class="bi bi-cash-coin me-1"></i> Catat Pembayaran Cicilan
            </button>
          @endif
        </div>
      </div>
    </div>

    <!-- Kolom Rincian Pembayaran / Cicilan -->
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Riwayat Transaksi & Cicilan Bulanan</h5>

          @if($tagihanUkt->transaksiPembayarans->isEmpty())
            <div class="alert alert-light border text-center py-4 text-muted">
              <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
              Belum ada riwayat pembayaran / cicilan yang dicatat untuk tagihan ini.
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>ID Gateway</th>
                    <th>Tanggal Bayar</th>
                    <th>Metode</th>
                    <th>Nominal Bayar</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($tagihanUkt->transaksiPembayarans as $index => $trx)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td class="font-monospace small text-primary">{{ $trx->gateway_transaction_id ?? '-' }}</td>
                      <td>{{ $trx->dibayar_pada ? $trx->dibayar_pada->format('d M Y H:i') : '-' }}</td>
                      <td><span class="badge bg-secondary">{{ $trx->metode_pembayaran }}</span></td>
                      <td class="fw-bold text-success">{{ $trx->format_nominal_bayar }}</td>
                      <td>
                        @if($trx->status === 'berhasil')
                          <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Berhasil</span>
                        @else
                          <span class="badge bg-warning text-dark">{{ ucfirst($trx->status) }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal Catat Cicilan Manual (Admin / Kasir / Mhs) -->
@if($tagihanUkt->sisa_tagihan > 0)
<div class="modal fade" id="modalCatatCicilan" tabindex="-1" aria-labelledby="modalCatatCicilanLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('tagihan-ukts.bayar', $tagihanUkt->id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCatatCicilanLabel">
            <i class="bi bi-cash-coin text-primary me-2"></i>Catat Pembayaran Cicilan
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <div class="p-2 border rounded bg-light">
              <div class="d-flex justify-content-between small text-muted">
                <span>Sisa Tagihan Saat Ini:</span>
                <strong class="text-danger">{{ $tagihanUkt->format_sisa }}</strong>
              </div>
            </div>
          </div>

          <div class="col-12">
            <label for="nominal_bayar" class="form-label">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="nominal_bayar" id="nominal_bayar" class="form-control"
                     min="10000" max="{{ $tagihanUkt->sisa_tagihan }}" step="1000"
                     value="{{ $tagihanUkt->sisa_tagihan }}" required>
            </div>
          </div>

          <div class="col-12">
            <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
            <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
              <option value="VA">Virtual Account (VA)</option>
              <option value="QRIS">QRIS</option>
              <option value="Transfer">Transfer Bank</option>
              <option value="Tunai" selected>Tunai / Loket Kampus</option>
              <option value="Retail">Retail (Indomaret / Alfamart)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle me-1"></i> Simpan Pembayaran
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

