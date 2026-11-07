@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Transaksi Pembayaran UKT</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Tagihan & Pembayaran</li>
      <li class="breadcrumb-item active">Transaksi Pembayaran</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<!-- Ringkasan Statistik Transaksi -->
<div class="row mb-3">
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card sales-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Total Transaksi <span>| Keseluruhan</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-light text-primary">
            <i class="bi bi-receipt"></i>
          </div>
          <div class="ps-3">
            <h6>{{ $stats['total_transaksi'] }} <span class="fs-6 fw-normal text-muted">Log</span></h6>
            <span class="text-muted small">Semua status transaksi</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card info-card revenue-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Dana Masuk <span>| Berhasil</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light text-success">
            <i class="bi bi-check2-circle"></i>
          </div>
          <div class="ps-3">
            <h6 class="text-success">Rp {{ number_format($stats['total_nominal_berhasil'], 0, ',', '.') }}</h6>
            <span class="badge bg-success">Status Berhasil</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card info-card customers-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Menunggu Bayar <span>| Pending</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-light text-warning">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <div class="ps-3">
            <h6 class="text-warning">{{ $stats['total_menunggu'] }} <span class="fs-6 fw-normal text-muted">Pending</span></h6>
            <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card pb-0">
      <div class="card-body d-flex flex-column justify-content-center py-3">
        <h5 class="card-title p-0 m-0 mb-2">Aksi Cepat</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateTransaksi">
          <i class="bi bi-plus-circle me-1"></i> Catat Transaksi Pembayaran
        </button>
      </div>
    </div>
  </div>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <h5 class="card-title p-0 m-0">Riwayat Transaksi & Pembayaran UKT</h5>
          </div>

          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

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

          <div class="table-responsive">
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'transaksi-pembayarans-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= Modal Catat Transaksi ================= -->
<div class="modal fade" id="modalCreateTransaksi" tabindex="-1" aria-labelledby="modalCreateTransaksiLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('transaksi-pembayarans.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreateTransaksiLabel">
            <i class="bi bi-cash-coin text-primary me-2"></i>Catat Transaksi Pembayaran
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label for="create_tagihan_ukt_id" class="form-label">Pilih Tagihan UKT Mahasiswa <span class="text-danger">*</span></label>
            <select name="tagihan_ukt_id" id="create_tagihan_ukt_id" class="form-select" required>
              <option value="" disabled selected>-- Pilih Tagihan UKT Mahasiswa --</option>
              @foreach($tagihanUkts as $t)
                <option value="{{ $t->id }}" data-sisa="{{ $t->sisa_tagihan }}">
                  {{ $t->nomor_tagihan }} - {{ $t->mahasiswa->nama_lengkap ?? '-' }} ({{ $t->institusi->nama_institusi ?? '-' }}) [Sisa: {{ $t->format_sisa }}]
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label for="create_nominal_rupiah" class="form-label">Nominal Bayar (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="nominal_rupiah" id="create_nominal_rupiah" class="form-control" placeholder="Contoh: 1500000" min="1000" step="1000" required>
            </div>
            <div class="form-text text-muted" id="hint_sisa">Nominal cicilan yang dibayarkan.</div>
          </div>

          <div class="col-md-6">
            <label for="create_metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
            <select name="metode_pembayaran" id="create_metode_pembayaran" class="form-select" required>
              <option value="VA" selected>Virtual Account (VA)</option>
              <option value="QRIS">QRIS</option>
              <option value="Transfer">Transfer Bank Manual</option>
              <option value="Tunai">Tunai / Loket Kampus</option>
              <option value="Retail">Retail (Indomaret / Alfamart)</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="create_status" class="form-label">Status Transaksi <span class="text-danger">*</span></label>
            <select name="status" id="create_status" class="form-select" required>
              <option value="berhasil" selected>Berhasil (Dana Diterima)</option>
              <option value="menunggu">Menunggu Pembayaran</option>
              <option value="gagal">Gagal</option>
              <option value="kedaluwarsa">Kedaluwarsa</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="create_dibayar_pada" class="form-label">Waktu Pembayaran</label>
            <input type="datetime-local" name="dibayar_pada" id="create_dibayar_pada" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
            <div class="form-text text-muted">Hanya berlaku jika status berhasil.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Transaksi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= Modal Edit Transaksi ================= -->
<div class="modal fade" id="modalEditTransaksi" tabindex="-1" aria-labelledby="modalEditTransaksiLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditTransaksi" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditTransaksiLabel">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Transaksi Pembayaran
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label class="form-label">ID Gateway Transaksi</label>
            <input type="text" id="edit_gateway_transaction_id" class="form-control font-monospace bg-light" readonly>
          </div>

          <div class="col-12">
            <label for="edit_nominal_rupiah" class="form-label">Nominal Bayar (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="nominal_rupiah" id="edit_nominal_rupiah" class="form-control" min="1000" step="1000" required>
            </div>
          </div>

          <div class="col-12">
            <label for="edit_metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
            <select name="metode_pembayaran" id="edit_metode_pembayaran" class="form-select" required>
              <option value="VA">Virtual Account (VA)</option>
              <option value="QRIS">QRIS</option>
              <option value="Transfer">Transfer Bank</option>
              <option value="Tunai">Tunai / Loket</option>
              <option value="Retail">Retail</option>
            </select>
          </div>

          <div class="col-12">
            <label for="edit_status" class="form-label">Status Transaksi <span class="text-danger">*</span></label>
            <select name="status" id="edit_status" class="form-select" required>
              <option value="berhasil">Berhasil (Dana Diterima)</option>
              <option value="menunggu">Menunggu Pembayaran</option>
              <option value="gagal">Gagal</option>
              <option value="kedaluwarsa">Kedaluwarsa</option>
            </select>
          </div>

          <div class="col-12">
            <label for="edit_dibayar_pada" class="form-label">Waktu Pembayaran</label>
            <input type="datetime-local" name="dibayar_pada" id="edit_dibayar_pada" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
    @if(app()->environment('production'))
      {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
      {!! $dataTable->scripts() !!}
    @endif

    <script>
      // Update hint nominal saat memilih tagihan
      $('#create_tagihan_ukt_id').on('change', function () {
        const sisa = $(this).find(':selected').data('sisa');
        if (sisa) {
          $('#create_nominal_rupiah').val(sisa);
          $('#hint_sisa').text('Maksimal sisa tagihan: Rp ' + parseInt(sisa).toLocaleString('id-ID'));
        }
      });

      // Buka Modal Edit Transaksi
      $(document).on('click', '.btn-edit-transaksi', function () {
        const id = $(this).data('id');
        const gatewayId = $(this).data('gateway_transaction_id');
        const nominal = $(this).data('nominal_rupiah');
        const metode = $(this).data('metode_pembayaran');
        const status = $(this).data('status');
        const dibayarPada = $(this).data('dibayar_pada');

        const updateUrl = '{{ url("/transaksi-pembayaran") }}/' + id;

        $('#formEditTransaksi').attr('action', updateUrl);
        $('#edit_gateway_transaction_id').val(gatewayId);
        $('#edit_nominal_rupiah').val(nominal);
        $('#edit_metode_pembayaran').val(metode);
        $('#edit_status').val(status);
        $('#edit_dibayar_pada').val(dibayarPada);

        const modal = new bootstrap.Modal(document.getElementById('modalEditTransaksi'));
        modal.show();
      });
    </script>
@endpush

