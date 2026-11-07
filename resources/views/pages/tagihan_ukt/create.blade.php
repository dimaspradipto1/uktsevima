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
              @if(auth()->user()->institusi_id)
                <input type="hidden" name="institusi_id" id="institusi_id" value="{{ auth()->user()->institusi_id }}">
                <input type="text" class="form-control bg-light fw-semibold" value="{{ auth()->user()->institusi->kode_institusi ?? '' }} - {{ auth()->user()->institusi->nama_institusi ?? '' }}" readonly>
                <div class="form-text text-muted">Terkunci otomatis pada institusi akun Anda.</div>
              @else
                <select name="institusi_id" id="institusi_id" class="form-select" required>
                  <option value="" disabled selected>-- Pilih Institusi Kampus --</option>
                  @foreach($institusis as $inst)
                    <option value="{{ $inst->id }}" {{ old('institusi_id') == $inst->id ? 'selected' : '' }}>
                      {{ $inst->kode_institusi }} - {{ $inst->nama_institusi }}
                    </option>
                  @endforeach
                </select>
                <div class="form-text text-muted">Super Admin: Pilih institusi penerbit tagihan.</div>
              @endif
            </div>

            <div class="col-md-6">
              <label for="periode_akademik_id" class="form-label">Periode Akademik / Semester <span class="text-danger">*</span></label>
              <select name="periode_akademik_id" id="periode_akademik_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Periode Semester --</option>
                @foreach($periodes as $p)
                  <option value="{{ $p->id }}" data-institusi="{{ $p->institusi_id }}" {{ old('periode_akademik_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->tahun_akademik }} - {{ $p->semester }} {{ !auth()->user()->institusi_id ? '(' . ($p->institusi->kode_institusi ?? '') . ')' : '' }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Mode Penerbitan Tagihan -->
            <div class="col-12 bg-light p-3 rounded border">
              <label class="form-label fw-bold mb-2 text-dark">
                <i class="bi bi-gear-wide-connected text-primary me-1"></i> Target Penerbitan Tagihan <span class="text-danger">*</span>
              </label>
              <div class="d-flex gap-4 flex-wrap">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="mode_penerbitan" id="mode_semua" value="semua" {{ old('mode_penerbitan', 'semua') === 'semua' ? 'checked' : '' }}>
                  <label class="form-check-label fw-bold text-success" for="mode_semua">
                    <i class="bi bi-people-fill me-1"></i> Seluruh Mahasiswa Kampus (Otomatis / Massal)
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="mode_penerbitan" id="mode_individu" value="individu" {{ old('mode_penerbitan') === 'individu' ? 'checked' : '' }}>
                  <label class="form-check-label fw-semibold text-secondary" for="mode_individu">
                    <i class="bi bi-person me-1"></i> Satu Mahasiswa Tertentu (Individu / Khusus)
                  </label>
                </div>
              </div>

              <div id="batch_info_box" class="alert alert-info py-2 small mb-0 mt-2">
                <i class="bi bi-magic me-1"></i>
                <strong>Mode Otomatis Aktif:</strong> Sistem akan otomatis menerbitkan tagihan UKT semester ini untuk <strong>seluruh mahasiswa aktif</strong> di kampus Anda yang belum memiliki tagihan pada semester tersebut.
              </div>
            </div>

            <div class="col-12" id="wrapper_mahasiswa_id" style="display: none;">
              <label for="mahasiswa_id" class="form-label fw-bold">Pilih Mahasiswa <span class="text-danger">*</span></label>
              <select name="mahasiswa_id" id="mahasiswa_id" class="form-select">
                <option value="" disabled selected>-- Pilih Mahasiswa --</option>
                @foreach($mahasiswas as $mhs)
                  <option value="{{ $mhs->id }}" data-institusi="{{ $mhs->institusi_id }}" {{ old('mahasiswa_id') == $mhs->id ? 'selected' : '' }}>
                    {{ $mhs->nomor_induk_mahasiswa }} - {{ $mhs->nama_lengkap }} {{ !auth()->user()->institusi_id ? '(' . ($mhs->institusi->nama_institusi ?? '') . ')' : '' }}
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const instSelect = document.getElementById('institusi_id');
    const periodeSelect = document.getElementById('periode_akademik_id');
    const mhsSelect = document.getElementById('mahasiswa_id');
    const radioSemua = document.getElementById('mode_semua');
    const radioIndividu = document.getElementById('mode_individu');
    const wrapperMhs = document.getElementById('wrapper_mahasiswa_id');
    const batchInfoBox = document.getElementById('batch_info_box');

    function toggleMode() {
      if (radioIndividu && radioIndividu.checked) {
        wrapperMhs.style.display = 'block';
        batchInfoBox.style.display = 'none';
        mhsSelect.setAttribute('required', 'required');
      } else {
        wrapperMhs.style.display = 'none';
        batchInfoBox.style.display = 'block';
        mhsSelect.removeAttribute('required');
        mhsSelect.value = '';
      }
    }

    if (radioSemua && radioIndividu) {
      radioSemua.addEventListener('change', toggleMode);
      radioIndividu.addEventListener('change', toggleMode);
      toggleMode();
    }

    if (instSelect && instSelect.tagName === 'SELECT') {
      instSelect.addEventListener('change', function () {
        const instId = this.value;
        Array.from(periodeSelect.options).forEach(opt => {
          if (!opt.value) return;
          opt.style.display = opt.dataset.institusi === instId ? '' : 'none';
        });
        Array.from(mhsSelect.options).forEach(opt => {
          if (!opt.value) return;
          opt.style.display = opt.dataset.institusi === instId ? '' : 'none';
        });
        periodeSelect.value = '';
        mhsSelect.value = '';
      });
    }
  });
</script>
@endpush
@endsection

