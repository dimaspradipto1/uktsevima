@extends('layouts.dashboard.template')

@section('content')
<div class="pagetitle">
  <h1>Edit Catatan Log Webhook</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('log-webhooks.index') }}">Log Webhook</a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Edit Log Webhook: {{ $logWebhook->gateway_event_id }}</h5>

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

          <form action="{{ route('log-webhooks.update', $logWebhook->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
              <div class="p-3 bg-light rounded small">
                <div>Institusi: <strong>{{ $logWebhook->institusi->nama_institusi ?? $logWebhook->kode_institusi_diterima ?? '-' }}</strong></div>
                <div>Event Gateway: <span class="badge bg-secondary">{{ $logWebhook->jenis_event }}</span></div>
                <div>HMAC Signature: <strong>{{ $logWebhook->signature_valid ? 'Valid' : 'Invalid' }}</strong></div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="status_pemprosesan" class="form-label">Status Pemrosesan <span class="text-danger">*</span></label>
              <select name="status_pemprosesan" id="status_pemprosesan" class="form-select" required>
                <option value="diproses" {{ old('status_pemprosesan', $logWebhook->status_pemprosesan) == 'diproses' ? 'selected' : '' }}>diproses</option>
                <option value="diterima" {{ old('status_pemprosesan', $logWebhook->status_pemprosesan) == 'diterima' ? 'selected' : '' }}>diterima</option>
                <option value="ditolak" {{ old('status_pemprosesan', $logWebhook->status_pemprosesan) == 'ditolak' ? 'selected' : '' }}>ditolak</option>
                <option value="duplikat" {{ old('status_pemprosesan', $logWebhook->status_pemprosesan) == 'duplikat' ? 'selected' : '' }}>duplikat</option>
                <option value="gagal" {{ old('status_pemprosesan', $logWebhook->status_pemprosesan) == 'gagal' ? 'selected' : '' }}>gagal</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="kode_respons_http" class="form-label">Kode Respons HTTP <span class="text-danger">*</span></label>
              <input type="number" name="kode_respons_http" id="kode_respons_http" class="form-control" value="{{ old('kode_respons_http', $logWebhook->kode_respons_http) }}" min="100" max="599" required>
            </div>

            <div class="col-12">
              <label for="pesan_kesalahan" class="form-label">Pesan / Keterangan Log</label>
              <textarea name="pesan_kesalahan" id="pesan_kesalahan" class="form-control" rows="3">{{ old('pesan_kesalahan', $logWebhook->pesan_kesalahan) }}</textarea>
            </div>

            <div class="text-end mt-4">
              <a href="{{ route('log-webhooks.index') }}" class="btn btn-secondary me-2">Kembali</a>
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

