@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Manajemen Tagihan UKT</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Tagihan & Pembayaran</li>
      <li class="breadcrumb-item active">Tagihan UKT</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<!-- Ringkasan Statistik Piutang & Pembayaran -->
<div class="row mb-3">
  <div class="col-xxl-3 col-md-6">
    <div class="card info-card sales-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Total Tagihan <span>| Keseluruhan</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-light text-primary">
            <i class="bi bi-file-earmark-text"></i>
          </div>
          <div class="ps-3">
            <h6>{{ $stats['total_tagihan'] }} <span class="fs-6 fw-normal text-muted">Tagihan</span></h6>
            <span class="text-muted small">Rp {{ number_format($stats['total_nominal'], 0, ',', '.') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card info-card revenue-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Dana Terkumpul <span>| Cicilan & Lunas</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light text-success">
            <i class="bi bi-cash-coin"></i>
          </div>
          <div class="ps-3">
            <h6 class="text-success">Rp {{ number_format($stats['total_terbayar'], 0, ',', '.') }}</h6>
            <span class="badge bg-success">{{ $stats['total_lunas'] }} Lunas</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card info-card customers-card pb-0">
      <div class="card-body">
        <h5 class="card-title">Sisa Piutang <span>| Belum Terbayar</span></h5>
        <div class="d-flex align-items-center">
          <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-light text-warning">
            <i class="bi bi-hourglass-split"></i>
          </div>
          <div class="ps-3">
            <h6 class="text-warning">Rp {{ number_format($stats['sisa_piutang'], 0, ',', '.') }}</h6>
            <span class="badge bg-warning text-dark">{{ $stats['total_belum_lunas'] }} Belum Lunas</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-md-6">
    <div class="card pb-0">
      <div class="card-body d-flex flex-column justify-content-center py-3">
        <h5 class="card-title p-0 m-0 mb-2">Aksi Cepat</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateTagihan">
          <i class="bi bi-plus-circle me-1"></i> Terbitkan Tagihan UKT
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
            <h5 class="card-title p-0 m-0">Daftar Tagihan UKT Multi-Kampus</h5>
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
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'tagihan-ukts-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= Modal Terbitkan Tagihan UKT ================= -->
<div class="modal fade" id="modalCreateTagihan" tabindex="-1" aria-labelledby="modalCreateTagihanLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('tagihan-ukts.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreateTagihanLabel">
            <i class="bi bi-receipt text-primary me-2"></i>Terbitkan Tagihan UKT Mahasiswa
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-md-6">
            <label for="create_institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
            @if(auth()->user()->institusi_id)
              <input type="hidden" name="institusi_id" id="create_institusi_id" value="{{ auth()->user()->institusi_id }}">
              <input type="text" class="form-control bg-light fw-semibold" value="{{ auth()->user()->institusi->kode_institusi ?? '' }} - {{ auth()->user()->institusi->nama_institusi ?? '' }}" readonly>
              <div class="form-text text-muted">Terkunci otomatis pada institusi Anda.</div>
            @else
              <select name="institusi_id" id="create_institusi_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}">{{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}</option>
                @endforeach
              </select>
              <div class="form-text text-muted">Super Admin: Pilih institusi kampus.</div>
            @endif
          </div>

          <div class="col-md-6">
            <label for="create_periode_akademik_id" class="form-label">Periode Akademik / Semester <span class="text-danger">*</span></label>
            <select name="periode_akademik_id" id="create_periode_akademik_id" class="form-select" required>
              <option value="" disabled selected>-- Pilih Periode Semester --</option>
              @foreach($periodes as $p)
                <option value="{{ $p->id }}" data-institusi="{{ $p->institusi_id }}">
                  {{ $p->tahun_akademik }} - {{ $p->semester }} ({{ $p->institusi->kode_institusi ?? '' }})
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label for="create_mahasiswa_id" class="form-label">Pilih Mahasiswa <span class="text-danger">*</span></label>
            <select name="mahasiswa_id" id="create_mahasiswa_id" class="form-select" required>
              <option value="" disabled selected>-- Pilih Mahasiswa --</option>
              @foreach($mahasiswas as $mhs)
                <option value="{{ $mhs->id }}" data-institusi="{{ $mhs->institusi_id }}">
                  {{ $mhs->nomor_induk_mahasiswa }} - {{ $mhs->nama_lengkap }} ({{ $mhs->institusi->nama_institusi ?? '' }})
                </option>
              @endforeach
            </select>
            <div class="form-text text-muted">Sesuai aturan ERD: 1 Mahasiswa hanya dapat memiliki 1 tagihan per semester di institusi terkait.</div>
          </div>

          <div class="col-md-6">
            <label for="create_nominal_tagihan" class="form-label">Nominal UKT Semester (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="nominal_tagihan" id="create_nominal_tagihan" class="form-control" placeholder="Contoh: 5000000" min="10000" step="1000" required>
            </div>
            <div class="form-text text-muted">Mahasiswa dapat mencicil nominal ini setiap bulan.</div>
          </div>

          <div class="col-md-6">
            <label for="create_tanggal_jatuh_tempo" class="form-label">Batas Waktu / Jatuh Tempo <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_jatuh_tempo" id="create_tanggal_jatuh_tempo" class="form-control" required value="{{ date('Y-m-d', strtotime('+30 days')) }}">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Terbitkan Tagihan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= Modal Edit Tagihan UKT ================= -->
<div class="modal fade" id="modalEditTagihan" tabindex="-1" aria-labelledby="modalEditTagihanLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditTagihan" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditTagihanLabel">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Tagihan UKT
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label for="edit_nominal_tagihan" class="form-label">Nominal Tagihan UKT (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="nominal_tagihan" id="edit_nominal_tagihan" class="form-control" min="10000" step="1000" required>
            </div>
          </div>

          <div class="col-12">
            <label for="edit_tanggal_jatuh_tempo" class="form-label">Batas Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_jatuh_tempo" id="edit_tanggal_jatuh_tempo" class="form-control" required>
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
      // Filter mahasiswa & periode sesuai institusi yang dipilih di modal tambah
      $('#create_institusi_id').on('change', function () {
        const instId = $(this).val();

        $('#create_mahasiswa_id option').each(function () {
          const mhsInst = $(this).data('institusi');
          if (!mhsInst || mhsInst == instId) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });

        $('#create_periode_akademik_id option').each(function () {
          const perInst = $(this).data('institusi');
          if (!perInst || perInst == instId) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      });

      // Buka Modal Edit Tagihan
      $(document).on('click', '.btn-edit-tagihan', function () {
        const id = $(this).data('id');
        const nominal = $(this).data('nominal_tagihan');
        const jatuhTempo = $(this).data('tanggal_jatuh_tempo');

        const updateUrl = '{{ url("/tagihan-ukt") }}/' + id;

        $('#formEditTagihan').attr('action', updateUrl);
        $('#edit_nominal_tagihan').val(nominal);
        $('#edit_tanggal_jatuh_tempo').val(jatuhTempo);

        const modal = new bootstrap.Modal(document.getElementById('modalEditTagihan'));
        modal.show();
      });
    </script>
@endpush

