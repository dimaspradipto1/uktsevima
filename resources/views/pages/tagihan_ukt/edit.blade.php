@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Edit Tagihan UKT</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('tagihan-ukts.index') }}">Tagihan UKT</a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Edit Tagihan UKT: {{ $tagihanUkt->nomor_tagihan }}</h5>

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

          <form action="{{ route('tagihan-ukts.update', $tagihanUkt->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
              <div class="p-3 bg-light rounded small">
                <div>Mahasiswa: <strong>{{ $tagihanUkt->mahasiswa->nama_lengkap }} ({{ $tagihanUkt->mahasiswa->nomor_induk_mahasiswa }})</strong></div>
                <div>Institusi: <strong>{{ $tagihanUkt->institusi->nama_institusi }}</strong></div>
                <div>Periode: <strong>{{ $tagihanUkt->periodeAkademik->tahun_akademik }} - {{ $tagihanUkt->periodeAkademik->semester }}</strong></div>
                <div>Sudah Dibayar: <strong class="text-success">{{ $tagihanUkt->format_dibayar }}</strong></div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="nominal_tagihan" class="form-label">Nominal UKT Semester (Rp) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="nominal_tagihan" id="nominal_tagihan" class="form-control"
                       value="{{ old('nominal_tagihan', $tagihanUkt->nominal_tagihan) }}"
                       min="{{ $tagihanUkt->total_dibayar }}" step="1000" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="tanggal_jatuh_tempo" class="form-label">Batas Jatuh Tempo <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="form-control"
                     value="{{ old('tanggal_jatuh_tempo', $tagihanUkt->tanggal_jatuh_tempo ? $tagihanUkt->tanggal_jatuh_tempo->format('Y-m-d') : '') }}" required>
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('tagihan-ukts.index') }}" class="btn btn-secondary me-2">Kembali</a>
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
