@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Edit Mahasiswa</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item">Data Master</li>
      <li class="breadcrumb-item"><a href="{{ route('mahasiswas.index') }}">Mahasiswa</a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-8">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Edit Data Mahasiswa: {{ $mahasiswa->nama_lengkap }}</h5>

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

          <form action="{{ route('mahasiswas.update', $mahasiswa->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
              <label for="institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
              <select name="institusi_id" id="institusi_id" class="form-select" required>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}" {{ old('institusi_id', $mahasiswa->institusi_id) == $inst->id ? 'selected' : '' }}>
                    {{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label for="nomor_induk_mahasiswa" class="form-label">Nomor Induk Mahasiswa (NIM) <span class="text-danger">*</span></label>
              <input type="text" name="nomor_induk_mahasiswa" id="nomor_induk_mahasiswa" class="form-control font-monospace" value="{{ old('nomor_induk_mahasiswa', $mahasiswa->nomor_induk_mahasiswa) }}" maxlength="50" required>
              <div class="form-text text-muted">NIM harus unik untuk institusi kampus terkait.</div>
            </div>

            <div class="col-12">
              <label for="nama_lengkap" class="form-label">Nama Lengkap Mahasiswa <span class="text-danger">*</span></label>
              <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" value="{{ old('nama_lengkap', $mahasiswa->nama_lengkap) }}" maxlength="150" required>
            </div>

            <div class="col-12">
              <label for="user_id" class="form-label">Tautkan Akun Login (Opsional)</label>
              <select name="user_id" id="user_id" class="form-select">
                <option value="">-- Belum Terhubung (Tanpa Akun) --</option>
                @foreach($availableUsers as $user)
                  <option value="{{ $user->id }}" {{ old('user_id', $mahasiswa->user_id) == $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }}) [Role: {{ $user->role }}]
                  </option>
                @endforeach
              </select>
              <div class="form-text text-muted">Satu akun login hanya dapat ditautkan ke satu profil mahasiswa.</div>
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('mahasiswas.index') }}" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
              </button>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</section>
@endsection

