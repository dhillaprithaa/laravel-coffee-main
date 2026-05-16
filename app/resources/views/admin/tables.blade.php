@extends('layouts.admin')
@section('title', 'Kelola Meja')
@section('page-title', 'Kelola Meja')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/tables.css') }}">
@endpush

@section('content')
  <div class="row">
    <div class="col-lg-8">
      <div class="table-card">
        <div class="table-card-header">
          <h5><i class="fas fa-chair mr-2"></i>Daftar Meja</h5>
          <span style="font-size:.8rem; opacity:.8;">{{ $tables->count() }} meja terdaftar</span>
        </div>
        <div class="p-0">
          <table class="table table-hover tbl mb-0">
            <thead>
              <tr>
                <th>No. Meja</th>
                <th>QR Code</th>
                <th>URL Scan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @if ($tables->count() > 0)
                @foreach ($tables as $table)
                  <tr>
                    <td><span class="meja-num">Meja {{ $table->number }}</span></td>
                    <td>
                      <div class="qrcode" data-url="{{ route('selforder.show', $table->id) }}">
                    </td>
                    <td>
                      <code>
                        <a href="{{ route('selforder.show', $table->id) }}" target="_blank"
                          style="color:#7b4a1e; font-size:.8rem;">
                          {{ route('selforder.show', $table->id) }}
                        </a>
                      </code>
                    </td>
                    <td>
                      <div style="display:flex; gap:6px; align-items:center;">
                        <a href="{{ route('admin.tables.code.show', $table) }}" target="_blank" class="action-btn"
                          style="background:#fef3cd; color:#856404; text-decoration:none;" title="Cetak QR Code">
                          <i class="fas fa-qrcode"></i>
                        </a>
                        @can('delete', $table)
                          <form method="POST" action="{{ route('admin.tables.destroy', $table) }}"
                            onsubmit="return confirm('Hapus Meja {{ $table->number }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn btn-del" title="Hapus Meja">
                              <i class="fas fa-trash"></i>
                            </button>
                          </form>
                        @endcan
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="4" class="text-center text-muted py-5">
                    <i class="fas fa-chair fa-3x mb-3 d-block"></i>
                    Belum ada meja. Tambahkan meja terlebih dahulu.
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      @can('create', \App\Models\Table::class)
        <div class="card elevation-2 mb-3" style="border-radius:14px; overflow:hidden;">
          <div style="background:linear-gradient(135deg,#4e2a04,#7b4a1e); color:white; padding:14px 18px;">
            <h6 style="margin:0; font-weight:700;"><i class="fas fa-plus mr-2"></i>Tambah Meja</h6>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('admin.tables.store') }}">
              @csrf
              <div class="form-group">
                <label style="font-weight:700; color:#4e2a04; font-size:.9rem;">Nomor Meja</label>
                <input type="number" name="number" class="form-control"
                  style="border-radius:10px; border:2px solid #e0d5c8;" placeholder="Contoh: 5" min="1" required>
                @error('number')
                  <small class="text-danger">{{ $message }}</small>
                @enderror
              </div>
              <button type="submit" class="btn btn-block"
                style="background:linear-gradient(135deg,#4e2a04,#7b4a1e); color:white; border-radius:10px; font-weight:700;">
                <i class="fas fa-plus mr-2"></i>Tambah Meja
              </button>
            </form>
          </div>
        </div>
      @endcan

      @can('generate', \App\Models\Table::class)
        <div class="gen-qr-card">
          <h6>
            <i class="fas fa-qrcode mr-2"></i>
            Generate QR Code
          </h6>
          <p style="color:rgba(245,235,224,.7); font-size:.85rem; margin-bottom:14px;">
            Buat QR Code untuk semua meja agar pelanggan bisa scan dan order mandiri.
          </p>
          <form method="POST" action="{{ route('admin.tables.code.generate') }}">
            @csrf
            <button type="submit" class="btn-gen"
              onclick="this.disabled=true; this.innerText='Mengunduh QR code'; this.form.submit();">
              <i class="fas fa-magic mr-2"></i>Generate Semua QR
            </button>
          </form>
        </div>
      @endcan
    </div>
  </div>

  @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
      const preview = document.querySelectorAll('.qrcode');
      preview.forEach(el => {
        const url = el.dataset.url;
        const qr = new QRCode(el, {
          text: url,
          width: 320,
          height: 320,
          colorDark: "#250f00",
          colorLight: "#ffffff",
          correctLevel: QRCode.CorrectLevel.L
        });
      });
    </script>
  @endpush
@endsection
