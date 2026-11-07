@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Data Mahasiswa</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Data Master</li>
      <li class="breadcrumb-item active">Mahasiswa</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <h5 class="card-title p-0 m-0">Daftar Mahasiswa Multi-Kampus</h5>
            <!-- Tombol Buka Modal Tambah -->
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMahasiswa">
              <i class="bi bi-person-plus-fill me-1"></i> Tambah Mahasiswa
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
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'mahasiswas-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================= Modal Tambah Mahasiswa ================= -->
<div class="modal fade" id="modalCreateMahasiswa" tabindex="-1" aria-labelledby="modalCreateMahasiswaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('mahasiswas.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreateMahasiswaLabel">
            <i class="bi bi-person-plus text-primary me-2"></i>Tambah Data Mahasiswa
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body row g-3">
          <div class="col-12">
            <label for="create_institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
            @if(auth()->user()->institusi_id)
              <input type="hidden" name="institusi_id" id="create_institusi_id" value="{{ auth()->user()->institusi_id }}">
              <input type="text" class="form-control bg-light fw-semibold" value="{{ auth()->user()->institusi->kode_institusi ?? '' }} - {{ auth()->user()->institusi->nama_institusi ?? '' }}" readonly>
              <div class="form-text text-muted">Terkunci otomatis pada institusi akun Anda.</div>
            @else
              <select name="institusi_id" id="create_institusi_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}">{{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}</option>
                @endforeach
              </select>
            @endif
          </div>

          <div class="col-12">
            <label for="create_nomor_induk_mahasiswa" class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
            <input type="text" name="nomor_induk_mahasiswa" id="create_nomor_induk_mahasiswa" class="form-control font-monospace" placeholder="Contoh: 202401001" maxlength="50" required>
            <div class="form-text text-muted">NIM harus unik di setiap institusi kampus.</div>
          </div>

          <div class="col-12">
            <label for="create_nama_lengkap" class="form-label">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
            <input type="text" name="nama_lengkap" id="create_nama_lengkap" class="form-control" placeholder="Contoh: Ahmad Fadilah" maxlength="150" required>
          </div>

          <div class="col-12">
            <label for="create_user_id" class="form-label">Tautkan Akun Login (Opsional)</label>
            <select name="user_id" id="create_user_id" class="form-select">
              <option value="">-- Belum Terhubung (Tanpa Akun) --</option>
              @foreach($availableUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) [Role: {{ $user->role }}]</option>
              @endforeach
            </select>
            <div class="form-text text-muted">Satu akun pengguna hanya dapat ditautkan ke satu profil mahasiswa.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Mahasiswa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================= Modal Edit Mahasiswa ================= -->
<div class="modal fade" id="modalEditMahasiswa" tabindex="-1" aria-labelledby="modalEditMahasiswaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditMahasiswa" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditMahasiswaLabel">
            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Data Mahasiswa
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

          <div class="col-12">
            <label for="edit_nomor_induk_mahasiswa" class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
            <input type="text" name="nomor_induk_mahasiswa" id="edit_nomor_induk_mahasiswa" class="form-control font-monospace" maxlength="50" required>
            <div class="form-text text-muted">NIM harus unik di setiap institusi kampus.</div>
          </div>

          <div class="col-12">
            <label for="edit_nama_lengkap" class="form-label">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
            <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control" maxlength="150" required>
          </div>

          <div class="col-12">
            <label for="edit_user_id" class="form-label">Tautkan Akun Login (Opsional)</label>
            <select name="user_id" id="edit_user_id" class="form-select">
              <option value="">-- Belum Terhubung (Tanpa Akun) --</option>
              @foreach($availableUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) [Role: {{ $user->role }}]</option>
              @endforeach
            </select>
            <div class="form-text text-muted">Satu akun pengguna hanya dapat ditautkan ke satu profil mahasiswa.</div>
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
      $(document).on('click', '.btn-edit-mahasiswa', function () {
        const id = $(this).data('id');
        const institusiId = $(this).data('institusi_id');
        const userId = $(this).data('user_id');
        const nim = $(this).data('nomor_induk_mahasiswa');
        const namaLengkap = $(this).data('nama_lengkap');

        const updateUrl = '{{ url("/mahasiswa") }}/' + id;

        $('#formEditMahasiswa').attr('action', updateUrl);
        $('#edit_institusi_id').val(institusiId);
        $('#edit_nomor_induk_mahasiswa').val(nim);
        $('#edit_nama_lengkap').val(namaLengkap);
        $('#edit_user_id').val(userId || '');

        const modal = new bootstrap.Modal(document.getElementById('modalEditMahasiswa'));
        modal.show();
      });
    </script>
@endpush

