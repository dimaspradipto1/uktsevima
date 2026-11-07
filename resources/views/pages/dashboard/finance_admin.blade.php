<div class="pagetitle">
  <h1>Dashboard Keuangan Kampus</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Keuangan</li>
      <li class="breadcrumb-item active">{{ $institusi->kode_institusi ?? 'Institusi' }}</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<!-- Banner Identitas Kampus -->
<div class="card bg-white shadow-sm mb-4 border-start border-4 border-primary">
  <div class="card-body p-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <!-- Plain text university name without badge/icon as requested -->
          <span class="fs-4 fw-bold text-dark">{{ $institusi->nama_institusi ?? 'Institusi Kampus' }}</span>
          <span class="text-muted font-monospace">({{ $institusi->kode_institusi ?? '-' }})</span>
        </div>
        <p class="mb-0 text-muted">
          Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong> (Finance Admin). Pantau arus kas UKT, tagihan jatuh tempo, dan rekonsiliasi pembayaran mahasiswa kampus ini.
        </p>
      </div>
      <div>
        <a href="{{ route('tagihan-ukts.create') }}" class="btn btn-primary me-2">
          <i class="bi bi-plus-circle me-1"></i> Terbitkan Tagihan UKT
        </a>
        <a href="{{ route('transaksi-pembayarans.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-receipt me-1"></i> Riwayat Pembayaran
        </a>
      </div>
    </div>
  </div>
</div>

<section class="section dashboard">
  <!-- 4 Kartu Metrik Keuangan Kampus -->
  <div class="row">
    <!-- Total Mahasiswa Kampus -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card customers-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Mahasiswa <span>| Kampus Ini</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-4 fw-bold text-dark mb-0">{{ number_format($totalMahasiswa, 0, ',', '.') }}</h6>
              <span class="text-muted small pt-1">Mahasiswa Terdaftar</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Tagihan UKT -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card sales-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Total Tagihan <span>| Semester Berjalan</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info-subtle text-info">
              <i class="bi bi-receipt"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-5 fw-bold text-dark mb-0">Rp {{ number_format($totalTagihanNominal, 0, ',', '.') }}</h6>
              <span class="text-muted small pt-1">{{ $totalTagihanCount }} Tagihan Diterbitkan</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dana Terkumpul (Berhasil) -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card revenue-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Dana Terkumpul <span>| Lunas & Cicilan</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success">
              <i class="bi bi-cash-stack"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-5 fw-bold text-success mb-0">Rp {{ number_format($totalDanaTerkumpul, 0, ',', '.') }}</h6>
              <span class="text-success small pt-1 fw-bold">{{ $pelunasanRate }}%</span> 
              <span class="text-muted small pt-2 ps-1">tertagih</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sisa Piutang Mahasiswa -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Sisa Piutang <span>| Belum Terbayar</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning">
              <i class="bi bi-exclamation-circle"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-5 fw-bold text-warning mb-0">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h6>
              <span class="text-muted small pt-1">Harus Ditagihkan</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Kolom Kiri -->
    <div class="col-lg-8">
      <!-- Tren Penerimaan Pembayaran UKT 7 Hari Terakhir -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title m-0">Tren Pembayaran Masuk <span>| 7 Hari Terakhir</span></h5>
            <span class="badge bg-light text-dark border">Kampus: {{ $institusi->kode_institusi ?? '-' }}</span>
          </div>
          <div id="chartTrenKampus"></div>
        </div>
      </div>

      <!-- Pembayaran Terbaru di Kampus Ini -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title m-0">Transaksi Pembayaran Terkini</h5>
            <a href="{{ route('transaksi-pembayarans.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>ID Transaksi</th>
                  <th>Mahasiswa</th>
                  <th>No. Tagihan</th>
                  <th class="text-end">Nominal</th>
                  <th class="text-center">Metode</th>
                  <th class="text-center">Status</th>
                  <th>Waktu</th>
                </tr>
              </thead>
              <tbody>
                @forelse($transaksiTerbaru as $trx)
                  <tr>
                    <td><span class="font-monospace fw-bold text-primary">{{ $trx->gateway_transaction_id }}</span></td>
                    <td>
                      <div class="fw-semibold text-dark">{{ $trx->tagihanUkt->mahasiswa->nama_lengkap ?? '-' }}</div>
                      <small class="text-muted font-monospace">{{ $trx->tagihanUkt->mahasiswa->nomor_induk_mahasiswa ?? '' }}</small>
                    </td>
                    <td><span class="font-monospace small text-muted">{{ $trx->tagihanUkt->nomor_tagihan ?? '-' }}</span></td>
                    <td class="text-end fw-bold text-dark">Rp {{ number_format($trx->nominal_rupiah, 0, ',', '.') }}</td>
                    <td class="text-center"><span class="badge bg-light text-secondary border">{{ $trx->metode_pembayaran ?? 'VA' }}</span></td>
                    <td class="text-center">
                      @if($trx->status === 'berhasil')
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Berhasil</span>
                      @elseif($trx->status === 'menunggu')
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>
                      @else
                        <span class="badge bg-danger">{{ ucfirst($trx->status) }}</span>
                      @endif
                    </td>
                    <td class="small text-muted">{{ $trx->created_at ? $trx->created_at->format('d/m H:i') : '-' }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-3">Belum ada transaksi di kampus ini.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tagihan Mendekati / Lewat Jatuh Tempo -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title m-0"><i class="bi bi-hourglass-split text-warning me-1"></i> Tagihan Perlu Perhatian <span>| Mendekati Jatuh Tempo</span></h5>
            <a href="{{ route('tagihan-ukts.index') }}" class="btn btn-sm btn-outline-warning">Filter Jatuh Tempo</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Mahasiswa</th>
                  <th>Periode</th>
                  <th class="text-center">Jatuh Tempo</th>
                  <th class="text-end">Sisa Tagihan</th>
                  <th class="text-center">Status</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($tagihanJatuhTempo as $tgh)
                  <tr>
                    <td>
                      <div class="fw-semibold text-dark">{{ $tgh->mahasiswa->nama_lengkap ?? '-' }}</div>
                      <small class="text-muted font-monospace">{{ $tgh->mahasiswa->nomor_induk_mahasiswa ?? '' }}</small>
                    </td>
                    <td class="small">{{ $tgh->periodeAkademik->tahun_akademik ?? '-' }} ({{ $tgh->periodeAkademik->semester ?? '' }})</td>
                    <td class="text-center">
                      @php
                        $isOverdue = $tgh->tanggal_jatuh_tempo && $tgh->tanggal_jatuh_tempo->isPast();
                      @endphp
                      <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning text-dark' }}">
                        {{ $tgh->tanggal_jatuh_tempo ? $tgh->tanggal_jatuh_tempo->format('d M Y') : '-' }}
                      </span>
                    </td>
                    <td class="text-end fw-bold text-danger">{{ $tgh->format_sisa }}</td>
                    <td class="text-center">
                      @if($tgh->status === 'dicicil')
                        <span class="badge bg-info"><i class="bi bi-pie-chart me-1"></i>Dicicil ({{ $tgh->persentase_lunas }}%)</span>
                      @else
                        <span class="badge bg-warning text-dark">Belum Bayar</span>
                      @endif
                    </td>
                    <td class="text-center">
                      <a href="{{ route('tagihan-ukts.show', $tgh->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye"></i> Detail
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-success py-3">
                      <i class="bi bi-check-circle me-1"></i> Tidak ada tagihan tertunggak yang mendesak saat ini.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="col-lg-4">
      <!-- Donut Chart Status Tagihan Kampus -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h5 class="card-title">Komposisi Status Tagihan</h5>
          <div id="chartStatusTagihanKampus"></div>
          <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
              <span class="text-muted"><i class="bi bi-circle-fill text-success me-2 font-xs"></i>Lunas</span>
              <span class="fw-bold">{{ $statusCounts['lunas'] }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
              <span class="text-muted"><i class="bi bi-circle-fill text-info me-2 font-xs"></i>Dicicil</span>
              <span class="fw-bold">{{ $statusCounts['dicicil'] }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
              <span class="text-muted"><i class="bi bi-circle-fill text-warning me-2 font-xs"></i>Belum Bayar</span>
              <span class="fw-bold">{{ $statusCounts['belum_bayar'] }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-1">
              <span class="text-muted"><i class="bi bi-circle-fill text-danger me-2 font-xs"></i>Kadaluarsa</span>
              <span class="fw-bold">{{ $statusCounts['kadaluarsa'] }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Ringkasan Tingkat Kolektibilitas UKT -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center p-4">
          <h5 class="card-title pb-0">Tingkat Kolektibilitas UKT</h5>
          <div class="my-3">
            <div class="display-5 fw-bold text-success">{{ $pelunasanRate }}%</div>
            <small class="text-muted">Target Pelunasan Semester Ini</small>
          </div>
          <div class="progress mb-3" style="height: 10px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $pelunasanRate }}%" aria-valuenow="{{ $pelunasanRate }}" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <div class="d-flex justify-content-between text-muted small">
            <span>Terkumpul: Rp {{ number_format($totalDanaTerkumpul, 0, ',', '.') }}</span>
            <span>Target: Rp {{ number_format($totalTagihanNominal, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>

      <!-- Shortcut & Aksi Cepat Admin Kampus -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Aksi Cepat</h5>
          <div class="d-grid gap-2">
            <a href="{{ route('tagihan-ukts.create') }}" class="btn btn-outline-primary text-start">
              <i class="bi bi-file-earmark-plus me-2"></i> Buat Tagihan Baru
            </a>
            <a href="{{ route('mahasiswas.create') }}" class="btn btn-outline-info text-start">
              <i class="bi bi-person-plus me-2"></i> Registrasi Mahasiswa
            </a>
            <a href="{{ route('transaksi-pembayarans.create') }}" class="btn btn-outline-success text-start">
              <i class="bi bi-credit-card me-2"></i> Input Transaksi Manual / Kas
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Chart Tren Pembayaran Kampus
    const trenKampusOptions = {
      series: [{
        name: 'Nominal Masuk (Rp)',
        data: @json($chartNominals)
      }, {
        name: 'Jumlah Transaksi',
        data: @json($chartCounts)
      }],
      chart: {
        height: 320,
        type: 'area',
        toolbar: { show: false }
      },
      colors: ['#2eca6a', '#4154f1'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      xaxis: {
        categories: @json($chartDates)
      },
      yaxis: [
        {
          title: { text: 'Nominal (Rp)' },
          labels: {
            formatter: function (value) {
              return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            }
          }
        },
        {
          opposite: true,
          title: { text: 'Transaksi' },
          labels: {
            formatter: function (value) {
              return Math.round(value);
            }
          }
        }
      ],
      tooltip: {
        y: {
          formatter: function (val, { seriesIndex }) {
            return seriesIndex === 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(val) : val + ' transaksi';
          }
        }
      }
    };
    new ApexCharts(document.querySelector("#chartTrenKampus"), trenKampusOptions).render();

    // 2. Chart Donut Status Tagihan Kampus
    const statusKampusOptions = {
      series: [
        {{ $statusCounts['lunas'] }},
        {{ $statusCounts['dicicil'] }},
        {{ $statusCounts['belum_bayar'] }},
        {{ $statusCounts['kadaluarsa'] }}
      ],
      chart: {
        height: 280,
        type: 'donut',
        toolbar: { show: false }
      },
      labels: ['Lunas', 'Dicicil', 'Belum Bayar', 'Kadaluarsa'],
      colors: ['#2eca6a', '#17a2b8', '#ffc107', '#dc3545'],
      legend: { position: 'bottom' },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: { width: 200 },
          legend: { position: 'bottom' }
        }
      }]
    };
    new ApexCharts(document.querySelector("#chartStatusTagihanKampus"), statusKampusOptions).render();
  });
</script>
@endpush
