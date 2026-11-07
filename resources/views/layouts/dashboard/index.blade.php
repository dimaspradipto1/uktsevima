@extends('layouts.dashboard.template')

@section('content')
  @if(isset($roleView) && $roleView === 'super_admin')
    @include('pages.dashboard.super_admin')
  @elseif(isset($roleView) && $roleView === 'finance_admin')
    @include('pages.dashboard.finance_admin')
  @elseif(isset($roleView) && $roleView === 'mahasiswa')
    @include('pages.dashboard.mahasiswa')
  @else
    <div class="alert alert-info">
      Memuat dashboard...
    </div>
  @endif
@endsection
