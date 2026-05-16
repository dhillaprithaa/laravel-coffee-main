@extends('layouts.admin')
@section('title', 'Edit Staff')
@section('page-title', 'Edit Staff')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/menus/form.css') }}">
@endpush

@section('content')
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="form-card">
        <div class="form-card-header">
          <h5><i class="fas fa-user-edit mr-2"></i>{{ $staff->name }}</h5>
        </div>
        <div class="form-card-body">
          @if ($errors->any())
            <div class="alert alert-danger" style="border-radius:10px;">
              <ul class="mb-0">
                <li>{{ $errors->first() }}</li>
              </ul>
            </div>
          @endif
          <form method="POST" action="{{ route('admin.staff.update', $staff) }}">
            @csrf @method('PUT')
            <div class="mb-4">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" name="name" class="form-control-custom" value="{{ old('name', $staff->name) }}"
                required>
            </div>
            <div class="mb-4">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control-custom"
                value="{{ old('username', $staff->username) }}" required>
            </div>
            <div class="mb-4">
              <label class="form-label" style="display:block;">Ganti Password</label>
              <small class="text-muted" style="font-size:.78rem;">Kosongkan jika tidak ingin mengubah password.</small>
            </div>
            <div class="row mb-4">
              <div class="col-6">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control-custom" placeholder="Min. 6 karakter">
              </div>
              <div class="col-6">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control-custom"
                  placeholder="Ulangi password">
              </div>
            </div>
            <div class="d-flex align-items-center">
              <button type="submit" class="btn-save">
                <i class="fas fa-save mr-2"></i>Update Staff
              </button>
              <a href="{{ route('admin.staff.index') }}" class="btn-back ml-3">
                <i class="fas fa-arrow-left mr-1"></i>Batal
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
