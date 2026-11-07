@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Data Periode Akademik</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Data Master</li>
      <li class="breadcrumb-item active">Periode Akademik</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <h5 class="card-title p-0 m-0">Daftar Periode Semester Kampus</h5>
            <!-- Tombol Buka Modal Tambah -->
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreatePeriode">
              <i class="bi bi-plus-circle me-1"></i> Tambah Periode
            </button>
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
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'periode-akademiks-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= Modal Tambah Periode Akademik ================= -->
<div class="modal fade" id="modalCreatePeriode" tabindex="-1" aria-labelledby="modalCreatePeriodeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('periode-akademiks.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreatePeriodeLabel">
            <i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Periode Akademik
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label for="create_institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
            <select name="institusi_id" id="create_institusi_id" class="form-select" required>
              <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
              @foreach($institusis as $inst)
                <option value="{{ $inst->id }}">{{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label for="create_tahun_akademik" class="form-label">Tahun Akademik <span class="text-danger">*</span></label>
            <input type="text" name="tahun_akademik" id="create_tahun_akademik" class="form-control font-monospace" placeholder="2024/2025" maxlength="9" required>
            <div class="form-text text-muted">Format: YYYY/YYYY</div>
          </div>

          <div class="col-md-6">
            <label for="create_semester" class="form-label">Semester <span class="text-danger">*</span></label>
            <select name="semester" id="create_semester" class="form-select" required>
              <option value="Ganjil" selected>Ganjil</option>
              <option value="Genap">Genap</option>
              <option value="Antara">Antara / Pendek</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="create_tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_mulai" id="create_tanggal_mulai" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="create_tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_selesai" id="create_tanggal_selesai" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Periode
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= Modal Edit Periode Akademik ================= -->
<div class="modal fade" id="modalEditPeriode" tabindex="-1" aria-labelledby="modalEditPeriodeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditPeriode" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditPeriodeLabel">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Periode Akademik
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label for="edit_institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
            <select name="institusi_id" id="edit_institusi_id" class="form-select" required>
              @foreach($institusis as $inst)
                <option value="{{ $inst->id }}">{{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label for="edit_tahun_akademik" class="form-label">Tahun Akademik <span class="text-danger">*</span></label>
            <input type="text" name="tahun_akademik" id="edit_tahun_akademik" class="form-control font-monospace" placeholder="2024/2025" maxlength="9" required>
            <div class="form-text text-muted">Format: YYYY/YYYY</div>
          </div>

          <div class="col-md-6">
            <label for="edit_semester" class="form-label">Semester <span class="text-danger">*</span></label>
            <select name="semester" id="edit_semester" class="form-select" required>
              <option value="Ganjil">Ganjil</option>
              <option value="Genap">Genap</option>
              <option value="Antara">Antara / Pendek</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label for="edit_tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" class="form-control" required>
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
      $(document).on('click', '.btn-edit-periode', function () {
        const id = $(this).data('id');
        const institusiId = $(this).data('institusi_id');
        const tahunAkademik = $(this).data('tahun_akademik');
        const semester = $(this).data('semester');
        const tanggalMulai = $(this).data('tanggal_mulai');
        const tanggalSelesai = $(this).data('tanggal_selesai');

        const updateUrl = '{{ url("/periode-akademik") }}/' + id;

        $('#formEditPeriode').attr('action', updateUrl);
        $('#edit_institusi_id').val(institusiId);
        $('#edit_tahun_akademik').val(tahunAkademik);
        $('#edit_semester').val(semester);
        $('#edit_tanggal_mulai').val(tanggalMulai);
        $('#edit_tanggal_selesai').val(tanggalSelesai);

        const modal = new bootstrap.Modal(document.getElementById('modalEditPeriode'));
        modal.show();
      });
    </script>
@endpush
