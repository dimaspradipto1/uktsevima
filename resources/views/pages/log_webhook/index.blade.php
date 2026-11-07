@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Log Webhook Payment Gateway</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Integrasi Gateway</li>
      <li class="breadcrumb-item active">Log Webhook</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<!-- Ringkasan Statistik Log Webhook -->
<div class="row mb-3">
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card sales-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Total Webhook <span>| Masuk</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-light text-primary">
            <i class="bi bi-broadcast"></i>
          </div>
          <div class="ps-3">
            <h6>{{ $stats['total_log'] }} <span class="fs-6 fw-normal text-muted">Payload</span></h6>
            <span class="text-muted small">Semua event gateway</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card info-card revenue-card pb-0">
      <div class="card-body">
        <h5 class="card-title">HMAC Valid <span>| Sukses</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light text-success">
            <i class="bi bi-shield-check"></i>
          </div>
          <div class="ps-3">
            <h6 class="text-success">{{ $stats['total_valid'] }}</h6>
            <span class="badge bg-success">Signature Terverifikasi</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card info-card customers-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Ditolak / Gagal <span>| Invalid</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-danger-light text-danger">
            <i class="bi bi-shield-x"></i>
          </div>
          <div class="ps-3">
            <h6 class="text-danger">{{ $stats['total_ditolak'] }}</h6>
            <span class="badge bg-danger">Signature Tidak Cocok</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card pb-0">
      <div class="card-body d-flex flex-column justify-content-center py-3">
        <h5 class="card-title p-0 m-0 mb-2">Simulasi Gateway</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSimulasiWebhook">
          <i class="bi bi-send-check me-1"></i> Uji Coba Webhook (HMAC)
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
            <h5 class="card-title p-0 m-0">Catatan Lengkap Percobaan Webhook (Termasuk Duplikat & Ditolak)</h5>
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
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'log-webhooks-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= Modal Simulasi Webhook ================= -->
<div class="modal fade" id="modalSimulasiWebhook" tabindex="-1" aria-labelledby="modalSimulasiWebhookLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('log-webhooks.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalSimulasiWebhookLabel">
            <i class="bi bi-broadcast text-primary me-2"></i>Simulasi Pengiriman Webhook Masuk
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <div class="alert alert-info py-2 small mb-0">
              <i class="bi bi-info-circle me-1"></i>
              Sesuai aturan ERD: Signature HMAC-SHA256 akan dihitung otomatis dari payload menggunakan <code>secret_key</code> institusi terkait.
            </div>
          </div>

          <div class="col-md-6">
            <label for="simulasi_institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
            <select name="institusi_id" id="simulasi_institusi_id" class="form-select" required>
              <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
              @foreach($institusis as $inst)
                <option value="{{ $inst->id }}">{{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label for="simulasi_transaksi_pembayaran_id" class="form-label">Tautkan Transaksi Pembayaran (Opsional)</label>
            <select name="transaksi_pembayaran_id" id="simulasi_transaksi_pembayaran_id" class="form-select">
              <option value="">-- Simulasi Transaksi Bebas --</option>
              @foreach($transaksis as $trx)
                <option value="{{ $trx->id }}">
                  {{ $trx->gateway_transaction_id }} - {{ $trx->tagihanUkt->mahasiswa->nama_lengkap ?? '-' }} [{{ $trx->format_nominal }}]
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label for="simulasi_jenis_event" class="form-label">Jenis Event Gateway <span class="text-danger">*</span></label>
            <select name="jenis_event" id="simulasi_jenis_event" class="form-select" required>
              <option value="payment.success" selected>payment.success (Pembayaran Berhasil)</option>
              <option value="payment.expired">payment.expired (Batas Waktu Habis)</option>
              <option value="payment.failed">payment.failed (Pembayaran Gagal)</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="simulasi_signature" class="form-label">Skenario Signature HMAC <span class="text-danger">*</span></label>
            <select name="simulasi_signature" id="simulasi_signature" class="form-select" required>
              <option value="valid" selected>Valid (HMAC Cocok dengan Secret Key Kampus)</option>
              <option value="invalid">Invalid (Simulasi Serangan / Key Salah &rarr; HTTP 401)</option>
            </select>
          </div>

          <div class="col-12">
            <label for="simulasi_gateway_event_id" class="form-label">Event ID Gateway (Opsional)</label>
            <input type="text" name="gateway_event_id" id="simulasi_gateway_event_id" class="form-control font-monospace" placeholder="Kosongkan untuk auto-generate atau isi event ID lama untuk tes deteksi duplikat">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send-check me-1"></i> Kirim Simulasi Webhook
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= Modal Rincian Payload JSON & HMAC ================= -->
<div class="modal fade" id="modalViewPayload" tabindex="-1" aria-labelledby="modalViewPayloadLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalViewPayloadLabel">
          <i class="bi bi-file-earmark-code text-primary me-2"></i>Payload JSON & Detail Webhook
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <div class="small text-muted">Event ID Gateway:</div>
            <div class="fw-bold font-monospace" id="view_event_id"></div>
          </div>
          <div class="col-md-6">
            <div class="small text-muted">Status Pemrosesan:</div>
            <div id="view_status_pemprosesan"></div>
          </div>
          <div class="col-md-6">
            <div class="small text-muted">Kode Respons HTTP:</div>
            <div class="fw-bold" id="view_http_code"></div>
          </div>
          <div class="col-md-6">
            <div class="small text-muted">Hasil Verifikasi HMAC:</div>
            <div id="view_hmac_status"></div>
          </div>
          <div class="col-12">
            <div class="small text-muted">Signature Diterima (Hex):</div>
            <div class="font-monospace small bg-light p-2 rounded text-break" id="view_signature"></div>
          </div>
          <div class="col-12" id="box_pesan_kesalahan">
            <div class="small text-muted">Keterangan / Log Pesan:</div>
            <div class="alert alert-warning py-1 small m-0" id="view_pesan_kesalahan"></div>
          </div>
        </div>

        <label class="form-label fw-bold">Payload JSON Asli (Raw Body):</label>
        <pre class="bg-dark text-light p-3 rounded" style="max-height: 250px; overflow-y: auto;" id="view_payload_json"></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= Modal Edit Status Log ================= -->
<div class="modal fade" id="modalEditLog" tabindex="-1" aria-labelledby="modalEditLogLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditLog" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditLogLabel">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Catatan Log Webhook
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label for="edit_status_pemprosesan" class="form-label">Status Pemrosesan <span class="text-danger">*</span></label>
            <select name="status_pemprosesan" id="edit_status_pemprosesan" class="form-select" required>
              <option value="diproses">diproses</option>
              <option value="diterima">diterima</option>
              <option value="ditolak">ditolak</option>
              <option value="duplikat">duplikat</option>
              <option value="gagal">gagal</option>
            </select>
          </div>

          <div class="col-12">
            <label for="edit_kode_respons_http" class="form-label">Kode Respons HTTP <span class="text-danger">*</span></label>
            <input type="number" name="kode_respons_http" id="edit_kode_respons_http" class="form-control" min="100" max="599" required>
          </div>

          <div class="col-12">
            <label for="edit_pesan_kesalahan" class="form-label">Pesan / Keterangan Log</label>
            <textarea name="pesan_kesalahan" id="edit_pesan_kesalahan" class="form-control" rows="3"></textarea>
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
      // Buka Modal Lihat Payload
      $(document).on('click', '.btn-view-payload', function () {
        const eventId = $(this).data('gateway_event_id');
        const status = $(this).data('status_pemprosesan');
        const httpCode = $(this).data('kode_respons_http');
        const sigValid = $(this).data('signature_valid') == 1;
        const signature = $(this).data('signature_diterima');
        const pesan = $(this).data('pesan_kesalahan');
        const payloadJson = $(this).data('payload_json');

        $('#view_event_id').text(eventId || '-');
        $('#view_status_pemprosesan').html('<span class="badge bg-secondary">' + status + '</span>');
        $('#view_http_code').text(httpCode || '-');
        $('#view_hmac_status').html(sigValid
          ? '<span class="badge bg-success">Valid HMAC</span>'
          : '<span class="badge bg-danger">Invalid Signature</span>'
        );
        $('#view_signature').text(signature || 'Tidak ada signature');

        if (pesan) {
          $('#box_pesan_kesalahan').show();
          $('#view_pesan_kesalahan').text(pesan);
        } else {
          $('#box_pesan_kesalahan').hide();
        }

        try {
          const parsed = typeof payloadJson === 'string' ? JSON.parse(payloadJson) : payloadJson;
          $('#view_payload_json').text(JSON.stringify(parsed, null, 2));
        } catch (e) {
          $('#view_payload_json').text(payloadJson || '-');
        }

        const modal = new bootstrap.Modal(document.getElementById('modalViewPayload'));
        modal.show();
      });

      // Buka Modal Edit Log
      $(document).on('click', '.btn-edit-log', function () {
        const id = $(this).data('id');
        const status = $(this).data('status_pemprosesan');
        const httpCode = $(this).data('kode_respons_http');
        const pesan = $(this).data('pesan_kesalahan');

        const updateUrl = '{{ url("/log-webhook") }}/' + id;

        $('#formEditLog').attr('action', updateUrl);
        $('#edit_status_pemprosesan').val(status);
        $('#edit_kode_respons_http').val(httpCode);
        $('#edit_pesan_kesalahan').val(pesan);

        const modal = new bootstrap.Modal(document.getElementById('modalEditLog'));
        modal.show();
      });
    </script>
@endpush

