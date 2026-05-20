@extends('layouts.admin')
@section('title', 'Edit Menu')
@section('page-title', 'Edit Menu')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/menus/form.css') }}">
@endpush

@section('content')
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="form-card">
        <div class="form-card-header">
          <h5><i class="fas fa-edit mr-2"></i>Edit Menu: {{ $menu->name }}</h5>
        </div>
        <div class="form-card-body">
          @if ($errors->any())
            <div class="alert alert-danger" style="border-radius:10px;">
              <ul class="mb-0">
                <li>{{ $errors->first() }}</li>
              </ul>
            </div>
          @endif
          <form method="POST" action="{{ route('admin.menus.update', $menu) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-4">
              <label class="form-label">Nama Menu</label>
              <input type="text" name="name" class="form-control-custom" value="{{ old('name', $menu->name) }}"
                required>
            </div>
            <div class="mb-4">
              <label class="form-label">category</label>
              <div class="category-wrap">
                @php
                  use App\Enums\MenuCategory;
                @endphp
                @foreach (MenuCategory::values() as $category)
                  <div class="category-option">
                    <input type="radio" name="category" id="kat-{{ $category }}" value="{{ $category }}"
                      @checked(old('category', $menu->category) === $category)>
                    <label for="kat-{{ $category }}">{{ $category->combined() }}</label>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" class="form-control-custom" rows="3"
                placeholder="Deskripsi menu...">{{ old('description', $menu->description) }}</textarea>
            </div>
            <div class="mb-4">
              <label class="form-label">Gambar Menu</label>
              @if ($menu->image)
                <div class="mb-2">
                  <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" style="max-width:200px; border-radius:8px;">
                </div>
              @endif
              <input type="file" name="image" class="form-control-custom" accept="image/jpg,image/jpeg,image/png,image/webp">
            </div>
            <div class="row mb-4">
              <div class="col-6">
                <label class="form-label">price (Rp)</label>
                <input type="number" name="price" class="form-control-custom" value="{{ old('price', $menu->price) }}"
                  min="0" required>
              </div>
              <div class="col-6">
                <label class="form-label">stock</label>
                <input type="number" name="stock" class="form-control-custom" value="{{ old('stock', $menu->stock) }}"
                  min="0" required>
              </div>
            </div>
            <div class="d-flex align-items-center">
              <button type="submit" class="btn-save">
                <i class="fas fa-save mr-2"></i>Update Menu
              </button>
              <a href="{{ route('admin.menus.index') }}" class="btn-back ml-3">
                <i class="fas fa-arrow-left mr-1"></i>Batal
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
