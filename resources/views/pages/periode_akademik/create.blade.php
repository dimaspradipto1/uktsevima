@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Tambah Periode Akademik</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('periode-akademiks.index') }}">Periode Akademik</a></li>
      <li class="breadcrumb-item active">Tambah</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-8">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Form Tambah Periode Akademik</h5>

          <form action="{{ route('periode-akademiks.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12">
              <label for="institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
              <select name="institusi_id" id="institusi_id" class="form-select @error('institusi_id') is-invalid @enderror" required>
                <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}" {{ old('institusi_id') == $inst->id ? 'selected' : '' }}>
                    {{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}
                  </option>
                @endforeach
              </select>
              @error('institusi_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="tahun_akademik" class="form-label">Tahun Akademik <span class="text-danger">*</span></label>
              <input type="text" name="tahun_akademik" id="tahun_akademik" class="form-control font-monospace @error('tahun_akademik') is-invalid @enderror" value="{{ old('tahun_akademik') }}" placeholder="Contoh: 2024/2025" maxlength="9" required>
              <div class="form-text text-muted">Format: YYYY/YYYY</div>
              @error('tahun_akademik')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
              <select name="semester" id="semester" class="form-select @error('semester') is-invalid @enderror" required>
                <option value="Ganjil" {{ old('semester') === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="Genap" {{ old('semester') === 'Genap' ? 'selected' : '' }}>Genap</option>
                <option value="Antara" {{ old('semester') === 'Antara' ? 'selected' : '' }}>Antara / Pendek</option>
              </select>
              @error('semester')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
              @error('tanggal_mulai')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}" required>
              @error('tanggal_selesai')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('periode-akademiks.index') }}" class="btn btn-secondary me-2">Batal</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Simpan Periode
              </button>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</section>
@endsection
