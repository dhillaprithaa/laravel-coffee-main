@extends('layouts.admin')
@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/menus/table.css') }}">
@endpush

@section('content')
  @if (session('success'))
    <div class="alert alert-success" style="border-radius:10px;">{{ session('success') }}</div>
  @endif
  <div class="card elevation-2" style="border-radius:14px; overflow:hidden;">
    <div class="menu-header-bar">
      <h5><i class="fas fa-users mr-2"></i>Daftar Pengguna</h5>
      @can('create', \App\Models\User::class)
        <a href="{{ route('admin.staff.create') }}" class="btn-add-menu">
          <i class="fas fa-plus mr-1"></i> Tambah Pengguna
        </a>
      @endcan
    </div>
    <div class="card-body p-0">
      <table class="table table-hover table-menu mb-0">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th width="140">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($staffList as $i => $staff)
            <tr>
              <td class="text-muted" style="font-size:.8rem;">{{ $i + 1 }}</td>
              <td>
                <div style="font-weight:700; color:#2d1200;">{{ $staff->name }}</div>
              </td>
              <td style="color:#7b4a1e;">{{ $staff->username }}</td>
              <td>
                <span class="{{ $staff->role->style() }}">{{ $staff->role->combined() }}</span>
              </td>
              <td>
                @can('update', $staff)
                  <a href="{{ route('admin.staff.edit', $staff) }}" class="action-btn btn-edit-menu mr-1">
                    <i class="fas fa-edit"></i>
                  </a>
                @endcan
                @can('delete', $staff)
                  <form method="POST" action="{{ route('admin.staff.destroy', $staff) }}"
                    class="form-delete" data-name="{{ $staff->name }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn btn-delete-menu">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-5">
                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                Belum ada staff.
                @can('create', \App\Models\User::class)
                  <a href="{{ route('admin.staff.create') }}">Tambah sekarang</a>
                @endcan
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.querySelectorAll('.form-delete').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: 'Hapus staff?',
          html: 'Yakin mau hapus <b>' + form.dataset.name + '</b>?<br> Staff akan dihapus permanen.',
          icon: 'warning',
          iconColor: '#7b4a1e',
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, hapus',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#7b4a1e',
          cancelButtonColor: '#a89a8c',
          reverseButtons: true,
          buttonsStyling: true,
          background: '#fffdf9',
        }).then(function (result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  </script>
@endpush