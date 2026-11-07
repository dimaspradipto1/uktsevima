@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Data Pengguna</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item active">Pengguna</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <h5 class="card-title p-0 m-0">Daftar Pengguna</h5>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Tambah Pengguna
            </a>
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

          <div class="table-responsive">
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'users-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- Modal Ubah Password Terpisah -->
<div class="modal fade" id="modalChangePassword" tabindex="-1" aria-labelledby="modalChangePasswordLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formChangePassword" method="POST" action="">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title" id="modalChangePasswordLabel">
            <i class="bi bi-key text-primary me-2"></i>Ubah Password: <span id="modalUserName" class="fw-bold"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="modalNewPassword" class="form-label">Password Baru <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password" id="modalNewPassword" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
              <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#modalNewPassword" title="Lihat/Sembunyikan">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label for="modalConfirmPassword" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password_confirmation" id="modalConfirmPassword" class="form-control" placeholder="Ketik ulang password baru" required minlength="6">
              <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#modalConfirmPassword" title="Lihat/Sembunyikan">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Simpan Password
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
      $(document).on('click', '.btn-change-password', function () {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        const url = '{{ url("/users") }}/' + userId + '/password';

        $('#modalUserName').text(userName);
        $('#formChangePassword').attr('action', url);
        $('#modalNewPassword').val('');
        $('#modalConfirmPassword').val('');

        const modal = new bootstrap.Modal(document.getElementById('modalChangePassword'));
        modal.show();
      });
    </script>
@endpush
