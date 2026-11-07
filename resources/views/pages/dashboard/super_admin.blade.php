<div class="pagetitle">
  <h1>Dashboard Super Administrator</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item active">Super Administrator</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<!-- Banner Selamat Datang -->
<div class="card bg-primary text-white shadow-sm mb-4 border-0">
  <div class="card-body p-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-shield-check me-2"></i>Selamat Datang, {{ auth()->user()->name }}</h4>
        <p class="mb-0 opacity-75">
          Pusat kendali multi-tenant: Monitor penerimaan UKT lintas perguruan tinggi, pergerakan transaksi, dan integritas webhook gateway secara real-time.
        </p>
      </div>
      <div>
        <a href="{{ route('tagihan-ukts.index') }}" class="btn btn-light fw-semibold text-primary me-2">
          <i class="bi bi-receipt me-1"></i> Data Tagihan
        </a>
        <a href="{{ route('log-webhooks.index') }}" class="btn btn-outline-light">
          <i class="bi bi-cpu me-1"></i> Audit Webhook
        </a>
      </div>
    </div>
  </div>
</div>

<section class="section dashboard">
  <!-- 4 Metrik Ringkasan Utama -->
  <div class="row">
    <!-- Total Institusi -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card sales-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Institusi <span>| Terdaftar</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary">
              <i class="bi bi-building"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-4 fw-bold text-dark mb-0">{{ $totalInstitusi }}</h6>
              <span class="text-muted small pt-1">Perguruan Tinggi Aktif</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Mahasiswa -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card customers-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Mahasiswa <span>| Global</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info-subtle text-info">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-4 fw-bold text-dark mb-0">{{ number_format($totalMahasiswa, 0, ',', '.') }}</h6>
              <span class="text-muted small pt-1">Total Mahasiswa Terdata</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Dana Masuk (Berhasil) -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card revenue-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Dana Masuk <span>| Berhasil</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success">
              <i class="bi bi-cash-stack"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-5 fw-bold text-success mb-0">Rp {{ number_format($totalDanaMasuk, 0, ',', '.') }}</h6>
              <span class="text-success small pt-1 fw-bold">{{ $globalPelunasanRate }}%</span> 
              <span class="text-muted small pt-2 ps-1">rasio pelunasan</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sisa Piutang UKT Global -->
    <div class="col-xxl-3 col-md-6">
      <div class="card info-card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title">Sisa Piutang <span>| UKT Global</span></h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning">
              <i class="bi bi-wallet2"></i>
            </div>
            <div class="ps-3">
              <h6 class="fs-5 fw-bold text-warning mb-0">Rp {{ number_format($totalPiutangGlobal, 0, ',', '.') }}</h6>
              <span class="text-muted small pt-1">dari total Rp {{ number_format($totalTagihanGlobal, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Kolom Kiri -->
    <div class="col-lg-8">
      <!-- Grafik Tren Pembayaran UKT 7 Hari Terakhir -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title m-0">Tren Pembayaran UKT <span>| 7 Hari Terakhir</span></h5>
            <span class="badge bg-light text-dark border">Real-Time Data</span>
          </div>
          <div id="chartTrenPembayaran"></div>
        </div>
      </div>

      <!-- Ringkasan Finansial Per Institusi / Kampus -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title m-0">Ringkasan Finansial Perguruan Tinggi</h5>
            <a href="{{ route('institusis.index') }}" class="btn btn-sm btn-outline-primary">Kelola Kampus</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 5%">No</th>
                  <th>Perguruan Tinggi</th>
                  <th class="text-center">Mahasiswa</th>
                  <th class="text-end">Total Tagihan</th>
                  <th class="text-end">Dana Terkumpul</th>
                  <th style="width: 25%" class="text-center">Progress Pelunasan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($institusiSummary as $idx => $inst)
                  <tr>
                    <td class="text-muted">{{ $idx + 1 }}</td>
                    <td>
                      <!-- University name: plain text without badge or icon as requested -->
                      <div class="fw-bold text-dark">{{ $inst['nama'] }}</div>
                      <small class="text-muted font-monospace">{{ $inst['kode'] }}</small>
                    </td>
                    <td class="text-center fw-semibold">{{ number_format($inst['mahasiswa_count'], 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($inst['total_tagihan'], 0, ',', '.') }}</td>
                    <td class="text-end text-success fw-bold">Rp {{ number_format($inst['dana_masuk'], 0, ',', '.') }}</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 8px;">
                          <div class="progress-bar bg-success" role="progressbar" style="width: {{ $inst['rate'] }}%" aria-valuenow="{{ $inst['rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="small fw-bold text-dark" style="min-width: 38px;">{{ $inst['rate'] }}%</span>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted py-3">Belum ada institusi terdaftar.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Transaksi Pembayaran Terbaru Lintas Kampus -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title m-0">Transaksi Pembayaran Terbaru <span>| Lintas Kampus</span></h5>
            <a href="{{ route('transaksi-pembayarans.index') }}" class="btn btn-sm btn-outline-primary">Semua Transaksi</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>ID Transaksi</th>
                  <th>Mahasiswa</th>
                  <th>Perguruan Tinggi</th>
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
                    <td>
                      <!-- University name: plain text without icon/badge background -->
                      <span class="text-dark">{{ $trx->institusi->nama_institusi ?? '-' }}</span>
                    </td>
                    <td class="text-end fw-bold text-dark">
                      Rp {{ number_format($trx->nominal_rupiah, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                      <span class="badge bg-light text-secondary border">{{ $trx->metode_pembayaran ?? 'VA' }}</span>
                    </td>
                    <td class="text-center">
                      @if($trx->status === 'berhasil')
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Berhasil</span>
                      @elseif($trx->status === 'menunggu')
                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>
                      @elseif($trx->status === 'gagal')
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Gagal</span>
                      @else
                        <span class="badge bg-secondary">{{ ucfirst($trx->status) }}</span>
                      @endif
                    </td>
                    <td class="small text-muted">
                      {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-3">Belum ada transaksi pembayaran tercatat.</td>
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
      <!-- Distribusi Status Tagihan Donut Chart -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h5 class="card-title">Distribusi Status Tagihan UKT</h5>
          <div id="chartStatusTagihan"></div>
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

      <!-- Statistik Webhook Gateway -->
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title m-0">Integritas Webhook Gateway</h5>
            <span class="badge bg-secondary">{{ $webhookStats['total'] }} Total</span>
          </div>
          <div class="row g-2 mb-3 text-center">
            <div class="col-6">
              <div class="p-2 border rounded bg-success-subtle">
                <div class="fw-bold fs-5 text-success">{{ $webhookStats['diproses'] }}</div>
                <small class="text-muted">Diproses (200)</small>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2 border rounded bg-danger-subtle">
                <div class="fw-bold fs-5 text-danger">{{ $webhookStats['ditolak'] + $webhookStats['gagal'] }}</div>
                <small class="text-muted">Ditolak / Gagal</small>
              </div>
            </div>
          </div>

          <h6 class="fw-bold small text-muted mb-2">Log Webhook Masuk Terkini:</h6>
          <div class="list-group list-group-flush small">
            @forelse($logWebhooks as $log)
              <div class="list-group-item px-0 py-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="font-monospace fw-bold text-dark">{{ Str::limit($log->gateway_event_id ?? 'No-ID', 16) }}</span>
                  @if($log->status_pemprosesan === 'diproses')
                    <span class="badge bg-success">Diproses</span>
                  @elseif($log->status_pemprosesan === 'duplikat')
                    <span class="badge bg-warning text-dark">Duplikat</span>
                  @else
                    <span class="badge bg-danger">{{ ucfirst($log->status_pemprosesan) }}</span>
                  @endif
                </div>
                <div class="d-flex justify-content-between text-muted font-xs">
                  <span>{{ $log->kode_institusi_diterima ?? ($log->institusi->kode_institusi ?? '-') }} &bull; {{ $log->jenis_event }}</span>
                  <span>{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
                </div>
              </div>
            @empty
              <div class="text-muted text-center py-2">Belum ada aktivitas webhook tercatat.</div>
            @endforelse
          </div>
          <div class="text-center mt-3">
            <a href="{{ route('log-webhooks.index') }}" class="btn btn-outline-secondary btn-sm w-100">
              <i class="bi bi-cpu me-1"></i> Buka Simulator & Audit Webhook
            </a>
          </div>
        </div>
      </div>

      <!-- Informasi Arsitektur & Keamanan Multi-Tenant -->
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Status Keamanan</h5>
          <ul class="list-unstyled mb-0 small">
            <li class="mb-2 d-flex align-items-start gap-2">
              <i class="bi bi-check2-circle text-success fs-6"></i>
              <div><strong>Isolasi Multi-Tenant:</strong> Kuat pada level institusi_id dengan compound index.</div>
            </li>
            <li class="mb-2 d-flex align-items-start gap-2">
              <i class="bi bi-check2-circle text-success fs-6"></i>
              <div><strong>Verifikasi Webhook:</strong> Validasi HMAC-SHA256 signature berbasis raw body payload.</div>
            </li>
            <li class="d-flex align-items-start gap-2">
              <i class="bi bi-check2-circle text-success fs-6"></i>
              <div><strong>Idempotensi:</strong> Perlindungan dari duplicate payload webhook event ID.</div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Chart Tren Pembayaran UKT 7 Hari Terakhir
    const trenOptions = {
      series: [{
        name: 'Nominal Dana Masuk (Rp)',
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
    new ApexCharts(document.querySelector("#chartTrenPembayaran"), trenOptions).render();

    // 2. Chart Distribusi Status Tagihan Donut Chart
    const statusOptions = {
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
    new ApexCharts(document.querySelector("#chartStatusTagihan"), statusOptions).render();
  });
</script>
@endpush

