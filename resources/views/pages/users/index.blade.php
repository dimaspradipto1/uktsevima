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

          <div class="table-responsive">
            {{ $dataTable->table(['class' => 'table table-striped table-bordered table-hover w-100', 'id' => 'users-table']) }}
          </div>

        </div>
      </div>

    </div>
  </div>
</section>
@endsection

@push('scripts')
    @if(app()->environment('production'))
      {!! str_replace('http:', 'https:', $dataTable->scripts()) !!}
    @else
      {!! $dataTable->scripts() !!}
    @endif
@endpush
