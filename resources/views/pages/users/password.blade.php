@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Ubah Password Pengguna</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
      <li class="breadcrumb-item active">Ubah Password</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-6">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <i class="bi bi-key me-2 text-primary"></i>Ubah Password: <strong>{{ $user->name }}</strong>
          </h5>

          <form action="{{ route('users.password.update', $user->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
              <label for="password" class="form-label">Password Baru <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required minlength="6">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password" title="Lihat/Sembunyikan">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru" required minlength="6">
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password_confirmation" title="Lihat/Sembunyikan">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Batal</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Simpan Password
              </button>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</section>
@endsection
