@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Terbitkan Tagihan UKT</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('tagihan-ukts.index') }}">Tagihan UKT</a></li>
      <li class="breadcrumb-item active">Terbitkan</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Form Terbitkan Tagihan UKT</h5>

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

          <form action="{{ route('tagihan-ukts.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-md-6">
              <label for="institusi_id" class="form-label">Institusi Kampus <span class="text-danger">*</span></label>
              <select name="institusi_id" id="institusi_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
                @foreach($institusis as $inst)
                  <option value="{{ $inst->id }}" {{ old('institusi_id') == $inst->id ? 'selected' : '' }}>
                    {{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="periode_akademik_id" class="form-label">Periode Akademik / Semester <span class="text-danger">*</span></label>
              <select name="periode_akademik_id" id="periode_akademik_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Periode Semester --</option>
                @foreach($periodes as $p)
                  <option value="{{ $p->id }}" {{ old('periode_akademik_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->tahun_akademik }} - {{ $p->semester }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label for="mahasiswa_id" class="form-label">Pilih Mahasiswa <span class="text-danger">*</span></label>
              <select name="mahasiswa_id" id="mahasiswa_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Mahasiswa --</option>
                @foreach($mahasiswas as $mhs)
                  <option value="{{ $mhs->id }}" {{ old('mahasiswa_id') == $mhs->id ? 'selected' : '' }}>
                    {{ $mhs->nomor_induk_mahasiswa }} - {{ $mhs->nama_lengkap }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="nominal_tagihan" class="form-label">Nominal UKT Semester (Rp) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="nominal_tagihan" id="nominal_tagihan" class="form-control" value="{{ old('nominal_tagihan') }}" min="10000" step="1000" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="tanggal_jatuh_tempo" class="form-label">Batas Jatuh Tempo <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="form-control" value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+30 days'))) }}" required>
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('tagihan-ukts.index') }}" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Terbitkan Tagihan UKT
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
