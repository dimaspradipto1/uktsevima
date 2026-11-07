@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Edit Pengguna</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-8">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Form Edit Pengguna</h5>

          <form action="{{ route('users.update', $user->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
              <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $user->name) }}" placeholder="Masukkan nama lengkap" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email', $user->email) }}" placeholder="contoh@gmail.com" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="role" class="form-label">Role Pengguna <span class="text-danger">*</span></label>
              <select name="role" class="form-select @error('role') is-invalid @enderror" id="role" required>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="mahasiswa" {{ old('role', $user->role) === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
              </select>
              @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="institusi_id" class="form-label">Institusi Kampus</label>
              <select name="institusi_id" class="form-select @error('institusi_id') is-invalid @enderror" id="institusi_id">
                <option value="">-- Semua Kampus (Superadmin) --</option>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}" {{ old('institusi_id', $user->institusi_id) == $inst->id ? 'selected' : '' }}>
                    {{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}
                  </option>
                @endforeach
              </select>
              <div class="form-text text-muted">Pilih kampus terkait (opsional bagi superadmin).</div>
              @error('institusi_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Batal</a>
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
