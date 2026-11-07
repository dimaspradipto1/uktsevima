@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Portal Tagihan UKT Mahasiswa</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Keuangan</li>
      <li class="breadcrumb-item active">Tagihan & Cicilan UKT</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

@if(!$mahasiswa)
  <div class="alert alert-warning" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Akun Anda belum ditautkan dengan profil Mahasiswa resmi di kampus. Silakan hubungi bagian Administrasi Kampus / Keuangan.
  </div>
@else

  <!-- Profil Ringkas Mahasiswa -->
  <div class="card bg-light border-0 shadow-sm mb-4">
    <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar-circle bg-primary text-white fs-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 50px; height: 50px;">
          <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
          <h5 class="m-0 fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</h5>
          <span class="text-muted small font-monospace">NIM: {{ $mahasiswa->nomor_induk_mahasiswa }}</span>
          <span class="badge bg-white text-primary border ms-2">
            <i class="bi bi-building me-1"></i>{{ $mahasiswa->institusi->nama_institusi ?? '-' }}
          </span>
        </div>
      </div>
      <div>
        <span class="badge bg-success-light text-success fs-6 border border-success">
          <i class="bi bi-person-check-fill me-1"></i>Status Mahasiswa Aktif
        </span>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Tagihan Semester Berjalan (Highlight Utama) -->
  @if($tagihanAktif)
    <div class="row">
      <div class="col-lg-8">
        <div class="card border-top border-primary border-4 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3 mt-2 flex-wrap gap-2">
              <div>
                <span class="badge bg-primary mb-1">Tagihan Semester Berjalan</span>
                <h4 class="card-title p-0 m-0 text-dark">
                  Periode: {{ $tagihanAktif->periodeAkademik->tahun_akademik }} (Semester {{ $tagihanAktif->periodeAkademik->semester }})
                </h4>
                <small class="text-muted font-monospace">No. Tagihan: {{ $tagihanAktif->nomor_tagihan }}</small>
              </div>
              <div class="text-end">
                @if($tagihanAktif->status === 'lunas')
                  <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>LUNAS</span>
                @elseif($tagihanAktif->status === 'dicicil')
                  <span class="badge bg-info text-dark fs-6"><i class="bi bi-pie-chart me-1"></i>DICICIL (BERJALAN)</span>
                @else
                  <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock me-1"></i>BELUM DIBAYAR</span>
                @endif
              </div>
            </div>

            <!-- Kartu Info Nominal & Cicilan -->
            <div class="row g-3 my-2">
              <div class="col-md-4">
                <div class="p-3 border rounded bg-light text-center">
                  <div class="small text-muted mb-1">Total Tagihan UKT</div>
                  <h5 class="fw-bold text-dark m-0">{{ $tagihanAktif->format_nominal }}</h5>
                </div>
              </div>

              <div class="col-md-4">
                <div class="p-3 border rounded bg-success-light text-center">
                  <div class="small text-success mb-1">Sudah Dibayar (Cicilan)</div>
                  <h5 class="fw-bold text-success m-0">{{ $tagihanAktif->format_dibayar }}</h5>
                </div>
              </div>

              <div class="col-md-4">
                <div class="p-3 border rounded bg-warning-light text-center">
                  <div class="small text-danger mb-1">Sisa Yang Harus Dibayar</div>
                  <h5 class="fw-bold text-danger m-0">{{ $tagihanAktif->format_sisa }}</h5>
                </div>
              </div>
            </div>

            <!-- Progress Bar Pelunasan -->
            <div class="my-3">
              <div class="d-flex justify-content-between small mb-1">
                <span class="fw-semibold">Progres Pembayaran UKT</span>
                <span class="fw-bold">{{ $tagihanAktif->persentase_lunas }}% Terpenuhi</span>
              </div>
              <div class="progress" style="height: 12px;">
                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $tagihanAktif->persentase_lunas }}%;"></div>
              </div>
            </div>

            <div class="alert alert-info py-2 d-flex align-items-center justify-content-between mb-0" role="alert">
              <div>
                <i class="bi bi-calendar-event me-2"></i>
                Batas Akhir / Jatuh Tempo: <strong>{{ $tagihanAktif->tanggal_jatuh_tempo ? $tagihanAktif->tanggal_jatuh_tempo->format('d M Y') : '-' }}</strong>
              </div>
              @if($tagihanAktif->sisa_tagihan > 0)
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBayarCicilan">
                  <i class="bi bi-wallet2 me-1"></i> Bayar / Cicil Bulan Ini
                </button>
              @endif
            </div>

          </div>
        </div>
      </div>

      <!-- Panduan Pembayaran Bertahap / Bulanan -->
      <div class="col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-info-circle text-primary me-2"></i>Sistem Bayar Bertahap</h5>
            <p class="small text-muted mb-2">
              Kampus mendukung pembayaran bertahap/cicilan bulanan selama semester berlangsung:
            </p>
            <ul class="small ps-3 text-secondary mb-3">
              <li>Pembayaran cicilan minimal <strong>Rp 10.000</strong> per transaksi.</li>
              <li>Bisa dibayar berkali-kali setiap bulan sampai batas jatuh tempo semester berakhir.</li>
              <li>Kwitansi digital otomatis tercatat setelah pembayaran berhasil.</li>
            </ul>

            @if($tagihanAktif->sisa_tagihan > 0)
              <button type="button" class="btn btn-success w-100 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBayarCicilan">
                <i class="bi bi-cash-stack me-1"></i> Bayar Cicilan Sekarang
              </button>
            @else
              <div class="alert alert-success text-center mb-0 py-2">
                <i class="bi bi-check2-all me-1"></i> Tagihan semester ini telah Lunas.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Riwayat Pembayaran / Cicilan Semester Ini -->
    <div class="card shadow-sm mt-2">
      <div class="card-body">
        <h5 class="card-title">Riwayat Transaksi Cicilan Semester Ini ({{ $tagihanAktif->periodeAkademik->tahun_akademik }})</h5>
        @if($tagihanAktif->transaksiPembayarans->isEmpty())
          <p class="text-muted small mb-0">Belum ada riwayat pembayaran cicilan untuk tagihan semester ini.</p>
        @else
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>No.</th>
                  <th>ID Transaksi</th>
                  <th>Tanggal Bayar</th>
                  <th>Metode</th>
                  <th>Nominal Cicilan</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($tagihanAktif->transaksiPembayarans as $idx => $trx)
                  <tr>
                    <td>{{ $idx + 1 }}</td>
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

    <!-- ================= Modal Pembayaran Cicilan Bulanan ================= -->
    <div class="modal fade" id="modalBayarCicilan" tabindex="-1" aria-labelledby="modalBayarCicilanLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="{{ route('tagihan-ukts.bayar', $tagihanAktif->id) }}" method="POST">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title" id="modalBayarCicilanLabel">
                <i class="bi bi-wallet2 text-primary me-2"></i>Bayar Cicilan UKT Bulan Ini
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body row g-3">
              <div class="col-12">
                <div class="p-2 border rounded bg-light">
                  <div class="d-flex justify-content-between small text-muted">
                    <span>Total Sisa Tagihan:</span>
                    <strong class="text-danger">{{ $tagihanAktif->format_sisa }}</strong>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <label for="nominal_bayar" class="form-label">Nominal Yang Ingin Dibayar (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">Rp</span>
                  <input type="number" name="nominal_bayar" id="nominal_bayar" class="form-control"
                         min="10000" max="{{ $tagihanAktif->sisa_tagihan }}" step="1000"
                         value="{{ min(1000000, $tagihanAktif->sisa_tagihan) }}" required>
                </div>
                <!-- Opsi Nominal Cepat -->
                <div class="d-flex gap-1 mt-2 flex-wrap">
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-quick-nom" data-amount="500000">Rp 500rb</button>
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-quick-nom" data-amount="1000000">Rp 1 Juta</button>
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-quick-nom" data-amount="2000000">Rp 2 Juta</button>
                  <button type="button" class="btn btn-outline-primary btn-sm btn-quick-nom" data-amount="{{ $tagihanAktif->sisa_tagihan }}">Lunasi Semua</button>
                </div>
              </div>

              <div class="col-12">
                <label for="metode_pembayaran" class="form-label">Pilih Metode Pembayaran <span class="text-danger">*</span></label>
                <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                  <option value="VA" selected>Virtual Account (BCA, Mandiri, BNI, BRI)</option>
                  <option value="QRIS">QRIS (GoPay, OVO, ShopeePay, Dana)</option>
                  <option value="Transfer">Transfer Bank Manual</option>
                  <option value="Retail">Retail (Indomaret / Alfamart)</option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-success">
                <i class="bi bi-shield-check me-1"></i> Konfirmasi Pembayaran
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @else
    <div class="card shadow-sm mb-4">
      <div class="card-body text-center py-5">
        <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
        <h5 class="fw-bold">Tidak Ada Tagihan Aktif</h5>
        <p class="text-muted small">Semua kewajiban tagihan UKT Anda telah terselesaikan atau belum diterbitkan oleh bagian keuangan.</p>
      </div>
    </div>
  @endif

  <!-- Seluruh Riwayat Tagihan Semester Lampau -->
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <h5 class="card-title">Daftar Semua Semester & Riwayat Tagihan</h5>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>No.</th>
              <th>No. Tagihan</th>
              <th>Periode Semester</th>
              <th>Total Tagihan</th>
              <th>Terbayar</th>
              <th>Sisa</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($riwayatTagihan as $idx => $tagihan)
              <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="font-monospace text-primary fw-bold">{{ $tagihan->nomor_tagihan }}</td>
                <td>{{ $tagihan->periodeAkademik->tahun_akademik ?? '-' }} ({{ $tagihan->periodeAkademik->semester ?? '-' }})</td>
                <td>{{ $tagihan->format_nominal }}</td>
                <td class="text-success">{{ $tagihan->format_dibayar }}</td>
                <td class="text-danger">{{ $tagihan->format_sisa }}</td>
                <td>
                  @if($tagihan->status === 'lunas')
                    <span class="badge bg-success">Lunas</span>
                  @elseif($tagihan->status === 'dicicil')
                    <span class="badge bg-info text-dark">Dicicil</span>
                  @else
                    <span class="badge bg-warning text-dark">Belum Bayar</span>
                  @endif
                </td>
                <td>
                  <a href="{{ route('tagihan-ukts.show', $tagihan->id) }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-receipt me-1"></i>Invoice
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-3">Belum ada riwayat tagihan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endif
@endsection

@push('scripts')
<script>
  $(document).on('click', '.btn-quick-nom', function () {
    const amount = $(this).data('amount');
    $('#nominal_bayar').val(amount);
  });
</script>
@endpush

