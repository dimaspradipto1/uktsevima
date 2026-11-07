<div class="pagetitle">
  <h1>Portal Tagihan Mahasiswa</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Mahasiswa</li>
      <li class="breadcrumb-item active">Tagihan & Cicilan</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

@if(!$mahasiswa)
  <div class="alert alert-warning shadow-sm border-0" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Akun login Anda belum ditautkan dengan data profil Mahasiswa. Silakan hubungi bagian Administrasi Kampus atau Keuangan.
  </div>
@else
  <!-- Banner Profil Mahasiswa -->
  <div class="card bg-white shadow-sm mb-4 border-0">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="avatar-circle bg-primary text-white fs-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px;">
          <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
          <h4 class="m-0 fw-bold text-dark">{{ $mahasiswa->nama_lengkap }}</h4>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="text-muted font-monospace small">NIM: {{ $mahasiswa->nomor_induk_mahasiswa }}</span>
            <span class="text-muted">&bull;</span>
            <!-- University name: plain text without icon and without badge background -->
            <span class="text-secondary fw-semibold">{{ $mahasiswa->institusi->nama_institusi ?? '-' }}</span>
          </div>
        </div>
      </div>
      <div>
        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
          <i class="bi bi-person-check-fill me-1"></i> Status Mahasiswa Aktif
        </span>
      </div>
    </div>
  </div>

  <section class="section dashboard">
    <!-- 3 Kartu Metrik Finansial Mahasiswa -->
    <div class="row">
      <!-- Total Beban UKT -->
      <div class="col-md-4">
        <div class="card info-card sales-card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Total Tagihan <span>| Seluruh Semester</span></h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary">
                <i class="bi bi-receipt"></i>
              </div>
              <div class="ps-3">
                <h6 class="fs-5 fw-bold text-dark mb-0">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h6>
                <span class="text-muted small pt-1">{{ $tagihans->count() }} semester tagihan</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sudah Dibayar -->
      <div class="col-md-4">
        <div class="card info-card revenue-card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Sudah Dibayar <span>| Terverifikasi</span></h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success">
                <i class="bi bi-check2-circle"></i>
              </div>
              <div class="ps-3">
                <h6 class="fs-5 fw-bold text-success mb-0">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</h6>
                <span class="text-success small pt-1 fw-bold">{{ $lunasCount }}</span> <span class="text-muted small pt-2 ps-1">semester lunas</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sisa Kewajiban Tagihan -->
      <div class="col-md-4">
        <div class="card info-card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title">Sisa Tagihan <span>| Kewajiban</span></h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning">
                <i class="bi bi-wallet2"></i>
              </div>
              <div class="ps-3">
                <h6 class="fs-5 fw-bold text-warning mb-0">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</h6>
                <span class="text-muted small pt-1">
                  @if($sisaTagihan == 0)
                    <span class="text-success fw-bold">Semua Lunas</span>
                  @else
                    {{ $belumLunasCount }} semester belum lunas
                  @endif
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Spotlight Hero Card: Tagihan Semester Berjalan -->
    @if($tagihanAktif)
      <div class="card shadow-sm border-top border-4 border-primary mb-4">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
              <span class="badge bg-primary mb-2">Tagihan Semester Berjalan</span>
              <h4 class="fw-bold text-dark m-0">
                Tahun Akademik: {{ $tagihanAktif->periodeAkademik->tahun_akademik ?? '-' }} 
                ({{ $tagihanAktif->periodeAkademik->semester ?? '-' }})
              </h4>
              <small class="text-muted font-monospace">Nomor Tagihan: {{ $tagihanAktif->nomor_tagihan }}</small>
            </div>
            <div class="text-end">
              @if($tagihanAktif->status === 'lunas')
                <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>LUNAS</span>
              @elseif($tagihanAktif->status === 'dicicil')
                <span class="badge bg-info text-white fs-6 px-3 py-2"><i class="bi bi-pie-chart me-1"></i>DICICIL ({{ $tagihanAktif->persentase_lunas }}%)</span>
              @elseif($tagihanAktif->status === 'kadaluarsa')
                <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i>KADALUARSA</span>
              @else
                <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-clock me-1"></i>BELUM BAYAR</span>
              @endif
            </div>
          </div>

          <div class="row g-3 my-2 text-center text-md-start">
            <div class="col-md-3">
              <span class="text-muted small">Total Tagihan</span>
              <div class="fs-5 fw-bold text-dark">{{ $tagihanAktif->format_nominal }}</div>
            </div>
            <div class="col-md-3">
              <span class="text-muted small">Sudah Dibayar</span>
              <div class="fs-5 fw-bold text-success">{{ $tagihanAktif->format_dibayar }}</div>
            </div>
            <div class="col-md-3">
              <span class="text-muted small">Sisa Tagihan</span>
              <div class="fs-5 fw-bold text-danger">{{ $tagihanAktif->format_sisa }}</div>
            </div>
            <div class="col-md-3">
              <span class="text-muted small">Batas Jatuh Tempo</span>
              <div class="fs-5 fw-semibold text-dark">
                {{ $tagihanAktif->tanggal_jatuh_tempo ? $tagihanAktif->tanggal_jatuh_tempo->format('d M Y') : '-' }}
              </div>
            </div>
          </div>

          <!-- Progress Bar Pelunasan -->
          <div class="mt-3">
            <div class="d-flex justify-content-between text-muted small mb-1">
              <span>Progres Pembayaran UKT Semester Ini</span>
              <span class="fw-bold text-dark">{{ $tagihanAktif->persentase_lunas }}%</span>
            </div>
            <div class="progress" style="height: 12px;">
              <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $tagihanAktif->persentase_lunas }}%" aria-valuenow="{{ $tagihanAktif->persentase_lunas }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>

          @if($tagihanAktif->status !== 'lunas')
            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div class="text-muted small">
                <i class="bi bi-info-circle text-primary me-1"></i>
                Anda dapat melakukan pembayaran bertahap (cicilan bulanan) atau langsung melunasi tagihan.
              </div>
              <div>
                <button type="button" class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#modalBayarCicilan{{ $tagihanAktif->id }}">
                  <i class="bi bi-credit-card-2-front me-1"></i> Bayar / Cicil Bulan Ini
                </button>
              </div>
            </div>

            <!-- Modal Bayar Cicilan Cepat -->
            <div class="modal fade" id="modalBayarCicilan{{ $tagihanAktif->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <form action="{{ route('tagihan-ukts.bayar', $tagihanAktif->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title fw-bold">
                        <i class="bi bi-cash-stack me-2"></i>Bayar / Cicil UKT
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                          <span class="text-muted">Total Beban Tagihan:</span>
                          <span class="fw-bold">{{ $tagihanAktif->format_nominal }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                          <span class="text-muted">Sudah Dibayar:</span>
                          <span class="text-success fw-bold">{{ $tagihanAktif->format_dibayar }}</span>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between">
                          <span class="fw-bold text-dark">Sisa Tagihan Saat Ini:</span>
                          <span class="text-danger fw-bold fs-6">{{ $tagihanAktif->format_sisa }}</span>
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="nominal_bayar" class="form-label fw-bold">Nominal Pembayaran / Cicilan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="nominal_bayar" id="nominal_bayar" class="form-control" min="10000" max="{{ $tagihanAktif->sisa_tagihan }}" value="{{ min(1000000, $tagihanAktif->sisa_tagihan) }}" required>
                        <div class="form-text">
                          Minimal Rp 10.000, Maksimal {{ $tagihanAktif->format_sisa }}.
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="metode_pembayaran" class="form-label fw-bold">Pilih Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" required>
                          <option value="VA" selected>Virtual Account Bank (BCA, Mandiri, BNI, BRI)</option>
                          <option value="QRIS">QRIS (GoPay, OVO, Dana, ShopeePay)</option>
                          <option value="Transfer">Bank Transfer</option>
                          <option value="Retail">Gerai Retail (Indomaret / Alfamart)</option>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i> Konfirmasi Pembayaran
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    @endif

    <div class="row">
      <!-- Kolom Kiri: Riwayat Transaksi & Seluruh Tagihan -->
      <div class="col-lg-8">
        <!-- Riwayat Transaksi Terakhir -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title m-0">Riwayat Pembayaran & Cicilan Terakhir</h5>
              <a href="{{ route('tagihan-ukts.index') }}" class="btn btn-sm btn-outline-primary">Portal Tagihan</a>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID Transaksi</th>
                    <th>Semester</th>
                    <th class="text-end">Nominal Dibayar</th>
                    <th class="text-center">Metode</th>
                    <th class="text-center">Status</th>
                    <th>Waktu Pembayaran</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($transaksiTerbaru as $trx)
                    <tr>
                      <td><span class="font-monospace fw-bold text-primary">{{ $trx->gateway_transaction_id }}</span></td>
                      <td class="small">{{ $trx->tagihanUkt->periodeAkademik->tahun_akademik ?? '-' }}</td>
                      <td class="text-end fw-bold text-success">Rp {{ number_format($trx->nominal_rupiah, 0, ',', '.') }}</td>
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
                      <td class="small text-muted">{{ $trx->dibayar_pada ? $trx->dibayar_pada->format('d/m/Y H:i') : ($trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-') }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted py-3">Belum ada riwayat transaksi pembayaran.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tagihan Seluruh Semester -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">Daftar Tagihan Seluruh Semester</h5>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Nomor Tagihan</th>
                    <th>Periode Semester</th>
                    <th class="text-end">Total UKT</th>
                    <th class="text-end">Telah Dibayar</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($tagihans as $tgh)
                    <tr>
                      <td><span class="font-monospace fw-bold text-dark">{{ $tgh->nomor_tagihan }}</span></td>
                      <td>{{ $tgh->periodeAkademik->tahun_akademik ?? '-' }} ({{ $tgh->periodeAkademik->semester ?? '-' }})</td>
                      <td class="text-end fw-semibold">{{ $tgh->format_nominal }}</td>
                      <td class="text-end text-success fw-semibold">{{ $tgh->format_dibayar }}</td>
                      <td class="text-center">
                        @if($tgh->status === 'lunas')
                          <span class="badge bg-success">Lunas</span>
                        @elseif($tgh->status === 'dicicil')
                          <span class="badge bg-info">Dicicil ({{ $tgh->persentase_lunas }}%)</span>
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
                      <td colspan="6" class="text-center text-muted py-3">Tidak ada catatan tagihan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Panduan & Informasi -->
      <div class="col-lg-4">
        <!-- Panduan Pembayaran -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-info-circle text-primary me-2"></i>Panduan Pembayaran</h5>
            <div class="accordion accordion-flush" id="accordionFaq">
              <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingOne">
                  <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne">
                    Virtual Account (VA)
                  </button>
                </h2>
                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                  <div class="accordion-body small text-muted">
                    Pilih metode VA pada formulir pembayaran, lalu salin nomor VA dan bayar melalui ATM, m-Banking, atau Internet Banking. Transaksi Anda akan otomatis terkonfirmasi detik itu juga melalui webhook gateway.
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingTwo">
                  <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo">
                    Pembayaran Cicilan Bulanan
                  </button>
                </h2>
                <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                  <div class="accordion-body small text-muted">
                    Sistem <strong>uktsevima</strong> mendukung pembayaran bertahap (cicilan). Anda dapat membayar sesuai kesiapan finansial hingga tanggal jatuh tempo yang ditetapkan kampus.
                  </div>
                </div>
              </div>
              <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingThree">
                  <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree">
                    Status KRS & Akademik
                  </button>
                </h2>
                <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFaq">
                  <div class="accordion-body small text-muted">
                    Setelah tagihan berstatus Lunas (atau memenuhi ambang cicilan yang ditentukan kampus), hak akses pengisian KRS pada sistem akademik kampus akan otomatis terbuka.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bantuan Keuangan Kampus -->
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-headset text-primary me-2"></i>Layanan Bantuan</h5>
            <p class="small text-muted">
              Mengalami kendala verifikasi pembayaran atau membutuhkan keringanan cicilan UKT? Silakan hubungi loket Biro Keuangan kampus Anda.
            </p>
            <div class="d-flex align-items-center gap-2 small text-muted mb-2">
              <i class="bi bi-clock"></i> Jam Operasional: Senin - Jumat (08:00 - 16:00 WIB)
            </div>
            <div class="d-flex align-items-center gap-2 small text-muted">
              <i class="bi bi-envelope"></i> Layanan: keuangan@sevima.com
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endif

