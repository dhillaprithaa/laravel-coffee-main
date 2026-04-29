@php
  use App\Enums\OrderType;
@endphp

@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
  <style>
    .small-box-coffee {
      background: linear-gradient(135deg, #4e2a04, #7b4a1e) !important;
    }

    .small-box-gold {
      background: linear-gradient(135deg, #b8860b, #d4a843) !important;
    }

    .small-box-dark {
      background: linear-gradient(135deg, #1a0a00, #3d1a00) !important;
    }

    .small-box .inner h3,
    .small-box .inner p {
      color: white !important;
    }

    .small-box .icon i {
      color: rgba(255, 255, 255, 0.2) !important;
    }

    .small-box-footer {
      background: rgba(0, 0, 0, 0.15) !important;
      color: white !important;
    }

    .small-box-footer:hover {
      background: rgba(0, 0, 0, 0.3) !important;
    }

    .card-qr {
      background: linear-gradient(135deg, #1a0a00 0%, #4e2a04 100%);
      border-radius: 16px !important;
      color: white;
      overflow: hidden;
      position: relative;
    }

    .card-qr::before {
      content: '';
      position: absolute;
      top: -40px;
      right: -40px;
      width: 140px;
      height: 140px;
      background: rgba(212, 168, 67, 0.15);
      border-radius: 50%;
    }

    .card-qr .card-body {
      position: relative;
      z-index: 1;
    }

    .card-qr h5 {
      color: #d4a843;
      font-weight: 700;
    }

    .card-qr p {
      color: rgba(245, 235, 224, 0.8);
      font-size: .9rem;
    }

    .stat-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
    }

    .recent-orders .table th {
      font-weight: 600;
      color: #4e2a04;
      border-top: none;
      background: #f5ebe0;
    }

    .badge-pending {
      background: #fff3cd;
      color: #856404;
      font-weight: 600;
    }

    .badge-selesai {
      background: #d1e7dd;
      color: #0a5436;
      font-weight: 600;
    }
  </style>
@endpush

@section('content')
  <div class="row">
    <div class="col-lg-4 col-md-6 col-12">
      <div class="small-box small-box-coffee elevation-3">
        <div class="inner">
          <h3>{{ $totalPesanan }}</h3>
          <p>Total Pesanan</p>
        </div>
        <div class="icon"><i class="fas fa-shopping-bag"></i></div>
        <a href="{{ route('admin.reports.index') }}" class="small-box-footer">
          Lihat Detail <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-12">
      <div class="small-box small-box-gold elevation-3">
        <div class="inner">
          <h3>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
          <p>Total Pendapatan</p>
        </div>
        <div class="icon"><i class="fas fa-coins"></i></div>
        <a href="{{ route('admin.reports.index') }}" class="small-box-footer">
          Lihat Detail <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
    <div class="col-lg-4 col-md-6 col-12">
      <div class="small-box small-box-dark elevation-3">
        <div class="inner">
          <h3>{{ $antrean }}</h3>
          <p>Antrean Aktif</p>
        </div>
        <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        <a href="{{ route('admin.orders.queue') }}" class="small-box-footer">
          Lihat Detail <i class="fas fa-arrow-circle-right"></i>
        </a>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-8">
      <div class="card elevation-2 recent-orders">
        <div class="card-header"
          style="background:linear-gradient(135deg,#4e2a04,#7b4a1e); color:white; border-radius:12px 12px 0 0 !important;">
          <h3 class="card-title">
            <i class="fas fa-list-alt mr-2"></i> Pesanan Terbaru
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Tipe</th>
                <th>Total</th>
                <th>Status</th>
                <th>Waktu</th>
              </tr>
            </thead>
            <tbody>
              @php
                $recentOrders = \App\Models\Order::with('payment')->latest()->take(8)->get();
              @endphp
              @if ($recentOrders->isEmpty())
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    Belum ada pesanan
                  </td>
                </tr>
              @else
                @foreach ($recentOrders as $order)
                  <tr>
                    <td><code style="color:#7b4a1e;">{{ $order->invoice }}</code></td>
                    <td>
                      @if ($order->type === OrderType::KASIR)
                        <span class="badge badge-info"><i class="fas fa-cash-register mr-1"></i>Kasir</span>
                      @else
                        <span class="badge badge-primary"><i class="fas fa-qrcode mr-1"></i>QR Self</span>
                      @endif
                    </td>
                    <td><strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong></td>
                    <td>
                      <span
                        class="badge badge-pill {{ $order->status === 'selesai' ? 'badge-selesai' : 'badge-pending' }}"
                        style="padding:.4rem .7rem;">
                        {{ $order->status->label() }}
                      </span>
                    </td>
                    <td style="font-size:.8rem; color:#888;">{{ $order->created_at->diffForHumans() }}</td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      @can('generate', \App\Models\Table::class)
        <div class="card card-qr elevation-3 mb-3">
          <div class="card-body">
            <h5>
              <i class="fas fa-qrcode mr-2"></i>
              Generate QR Code
            </h5>
            <p>Buat QR Code untuk semua meja agar pelanggan bisa scan dan order mandiri.</p>
            <form action="{{ route('admin.tables.code.generate') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-gold btn-block"
                onclick="this.disabled=true; this.innerText='Mengunduh QR code'; this.form.submit();">
                <i class="fas fa-magic mr-2"></i>Generate Semua QR
              </button>
            </form>
          </div>
        </div>
      @endcan
      <div class="card elevation-2">
        <div class="card-header"
          style="background:linear-gradient(135deg,#1a0a00,#3d1a00); color:white; border-radius:12px 12px 0 0 !important;">
          <h3 class="card-title">
            <i class="fas fa-info-circle mr-2"></i> Ringkasan Hari Ini
          </h3>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <div class="stat-icon mr-3" style="background:#f5ebe0;">
                <i class="fas fa-utensils" style="color:#7b4a1e;"></i>
              </div>
              <div>
                <div style="font-size:.8rem; color:#888;">Pesanan Hari Ini</div>
                <div style="font-weight:700; color:#4e2a04;">
                  {{ \App\Models\Order::whereDate('created_at', today())->count() }}
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <div class="stat-icon mr-3" style="background:#fff3cd;">
                <i class="fas fa-coins" style="color:#b8860b;"></i>
              </div>
              <div>
                <div style="font-size:.8rem; color:#888;">Pendapatan Hari Ini</div>
                <div style="font-weight:700; color:#4e2a04;">
                  Rp
                  {{ number_format(\App\Models\Order::whereDate('created_at', today())->where('status', 'selesai')->sum('grand_total'), 0, ',', '.') }}
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <div class="stat-icon mr-3" style="background:#d1e7dd;">
                <i class="fas fa-check-circle" style="color:#0a5436;"></i>
              </div>
              <div>
                <div style="font-size:.8rem; color:#888;">Selesai Hari Ini</div>
                <div style="font-weight:700; color:#4e2a04;">
                  {{ \App\Models\Order::whereDate('created_at', today())->where('status', 'selesai')->count() }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
