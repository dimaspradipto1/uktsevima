@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Edit Institusi</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('institusis.index') }}">Institusi</a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-8">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Form Edit Institusi: <strong>{{ $institusi->nama_institusi }}</strong></h5>

          <form action="{{ route('institusis.update', $institusi->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
              <label for="kode_institusi" class="form-label">Kode Institusi <span class="text-danger">*</span></label>
              <input type="text" name="kode_institusi" class="form-control font-monospace @error('kode_institusi') is-invalid @enderror" id="kode_institusi" value="{{ old('kode_institusi', $institusi->kode_institusi) }}" placeholder="Maks. 10 karakter" maxlength="10" required style="text-transform: uppercase;">
              <div class="form-text text-muted">Kode unik pengenal kampus (maksimal 10 karakter).</div>
              @error('kode_institusi')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="status" class="form-label">Status Institusi <span class="text-danger">*</span></label>
              <select name="status" class="form-select @error('status') is-invalid @enderror" id="status" required>
                <option value="1" {{ old('status', $institusi->status ? '1' : '0') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ old('status', $institusi->status ? '1' : '0') == '0' ? 'selected' : '' }}>Nonaktif</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label for="nama_institusi" class="form-label">Nama Institusi Kampus <span class="text-danger">*</span></label>
              <input type="text" name="nama_institusi" class="form-control @error('nama_institusi') is-invalid @enderror" id="nama_institusi" value="{{ old('nama_institusi', $institusi->nama_institusi) }}" placeholder="Contoh: Universitas Sevima Nusantara" required>
              @error('nama_institusi')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12">
              <label for="secret_key" class="form-label">Secret Key (HMAC-SHA256) <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="text" name="secret_key" id="secret_key" class="form-control font-monospace @error('secret_key') is-invalid @enderror" value="{{ old('secret_key', $institusi->secret_key) }}" placeholder="64 karakter hex string" maxlength="64" required>
                <button type="button" class="btn btn-outline-secondary" id="btnGenSecret" title="Generate Secret Key Baru">
                  <i class="bi bi-arrow-clockwise me-1"></i> Acak Ulang
                </button>
              </div>
              <div class="form-text text-muted">Kunci rahasia 64-karakter untuk validasi tanda tangan HMAC webhook pembayaran.</div>
              @error('secret_key')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('institusis.index') }}" class="btn btn-secondary me-2">Batal</a>
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

@push('scripts')
<script>
  document.getElementById('btnGenSecret').addEventListener('click', function () {
    Swal.fire({
      title: 'Acak Ulang Secret Key?',
      text: 'Pastikan sistem webhook gateway diperbarui jika secret key ini diubah.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#0d6efd',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> Ya, Buat Baru',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        const arr = new Uint8Array(32);
        window.crypto.getRandomValues(arr);
        const hex = Array.from(arr).map(b => b.toString(16).padStart(2, '0')).join('');
        document.getElementById('secret_key').value = hex;
      }
    });
  });
</script>
@endpush

