@php
  use App\Enums\MenuCategory;
@endphp
@extends('layouts.admin')
@section('title', 'Menu Produk')
@section('page-title', 'Menu Produk')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/menus/table.css') }}">
@endpush

@section('content')
  <div class="card elevation-2" style="border-radius:14px; overflow:hidden;">
    <div class="menu-header-bar">
      <h5><i class="fas fa-utensils mr-2"></i>Daftar Menu Produk</h5>
      @can('create', \App\Models\Menu::class)
        <a href="{{ route('admin.menus.create') }}" class="btn-add-menu">
          <i class="fas fa-plus mr-1"></i> Tambah Menu
        </a>
      @endcan
    </div>
    <div class="card-body p-0">
      <table class="table table-hover table-menu mb-0">
        <thead>
          <tr>
            <th width="40">#</th>
            <th width="70">Gambar</th>
            <th>Nama Menu</th>
            <th>Deskripsi</th>
            <th>category</th>
            <th>price</th>
            <th>stock</th>
            <th width="140">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @if ($menus->count() > 0)
            @foreach ($menus as $i => $menu)
              <tr id="row-{{ $menu->id }}">
                <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
                <td>
                  @if ($menu->image)
                    <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}"
                      style="width:50px; height:50px; object-fit:cover; border-radius:6px;">
                  @else
                    <span class="text-muted" style="font-size:.75rem;">—</span>
                  @endif
                </td>
                <td>
                  <div style="font-weight:700; color:#2d1200;">{{ $menu->name }}</div>
                </td>
                <td style="max-width:180px;">
                  <span class="text-muted" style="font-size:.85rem;">{{ $menu->description ?? '—' }}</span>
                </td>
                <td>
                  <span class="{{ $menu->category->style() }}">{{ $menu->category->combined() }}</span>
                </td>
                <td style="font-weight:700; color:#d4a843;">Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                <td>
                  @php $stockClass = $menu->stock <= 0 ? 'stock-out' : ($menu->stock <= 5 ? 'stock-low' : 'stock-ok'); @endphp
                  <span class="stock-badge {{ $stockClass }}" id="stock-badge-{{ $menu->id }}">
                    {{ $menu->stock }} pcs
                  </span>
                  <input type="number" class="stock-input ml-2" id="stock-input-{{ $menu->id }}"
                    value="{{ $menu->stock }}" min="0" data-menu-id="{{ $menu->id }}">
                </td>
                <td>
                  @can('update', $menu)
                    <a href="{{ route('admin.menus.edit', $menu) }}" class="action-btn btn-edit-menu mr-1">
                      <i class="fas fa-edit"></i>
                    </a>
                  @endcan
                  @can('delete', $menu)
                    <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" style="display:inline;"
                      onsubmit="return confirm('Hapus menu {{ $menu->name }}?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="action-btn btn-delete-menu">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  @endcan
                </td>
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="8" class="text-center text-muted py-5">
                <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                Belum ada menu.
                @can('create', \App\Models\Menu::class)
                  <a href="{{ route('admin.menus.create') }}">Tambah sekarang</a>
                @endcan
              </td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.querySelectorAll('.stock-input').forEach(input => {
      input.addEventListener('change', function() {
        const menuId = this.dataset.menuId;
        const stock = this.value;

        fetch(`/admin/menus/${menuId}/stock`, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json',
            },
            body: JSON.stringify({
              stock: parseInt(stock)
            }),
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              const badge = document.getElementById('stock-badge-' + menuId);
              badge.textContent = data.stock + ' pcs';
              badge.className = 'stock-badge ' + (data.stock <= 0 ? 'stock-out' : data.stock <= 5 ?
                'stock-low' :
                'stock-ok');
            }
          });
      });
    });
  </script>
@endpush
