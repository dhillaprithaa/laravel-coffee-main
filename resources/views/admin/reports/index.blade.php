@php
  use Carbon\Carbon;
  use App\Enums\RoleType;
  use App\Enums\OrderType;
  use App\Enums\OrderStatus;
  use App\Enums\PaymentMethod;
  use Illuminate\Support\Facades\Auth;

  $reportTitle = Auth::user()->role === RoleType::STAFF ? 'Laporan Transaksi' : 'Laporan Penjualan';
@endphp

@extends('layouts.admin')
@section('title', $reportTitle)
@section('page-title', $reportTitle)

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/reports/index.css') }}">
@endpush

@section('content')
  <form method="GET" action="{{ route('admin.reports.index') }}" id="filterForm">
    @if (Auth::user()->role === RoleType::PIMPINAN)
      <div class="filter-bar mb-4">
        <label><i class="fas fa-calendar-start mr-2"></i>Dari:</label>
        <input type="date" name="start" value="{{ $start }}"
          onchange="document.getElementById('filterForm').submit()">
        <label style="margin-left:1.5rem;"><i class="fas fa-calendar-end mr-2"></i>Sampai:</label>
        <input type="date" name="end" value="{{ $end }}"
          onchange="document.getElementById('filterForm').submit()">
        <span style="color:#aaa; font-size:.85rem; margin-left:auto;">
          Menampilkan data:
          <strong style="color:#4e2a04;">{{ Carbon::parse($start)->format('d M Y') }} —
            {{ Carbon::parse($end)->format('d M Y') }}</strong>
        </span>
      </div>
    @endif

    <div class="filter-bar mb-4" style="flex-wrap:wrap; gap:10px;">
      <select name="type" class="filter-select" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Tipe</option>
        @foreach (OrderType::cases() as $type)
          <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>
        @endforeach
      </select>

      <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Status</option>
        @foreach (OrderStatus::cases() as $status)
          <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
        @endforeach
      </select>

      <select name="method" class="filter-select" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Pembayaran</option>
        @foreach (PaymentMethod::cases() as $method)
          <option value="{{ $method->value }}" @selected($filters['method'] === $method->value)>{{ $method->label() }}</option>
        @endforeach
      </select>

      <div style="display:flex; align-items:center; gap:6px; margin-left:auto;">
        <input type="text" name="invoice" value="{{ $filters['invoice'] }}" placeholder="Cari invoice..."
          class="filter-input"
          oninput="clearTimeout(window._st); window._st = setTimeout(() => document.getElementById('filterForm').submit(), 400)">

        @if (array_filter($filters))
          <a href="{{ route('admin.reports.index') }}" class="btn-filter">
            <i class="fas fa-times mr-1"></i>Reset
          </a>
        @endif

        <a href="{{ route('admin.reports.export', array_merge(['bulan' => $start], array_filter($filters))) }}"
          class="btn-export">
          <i class="fas fa-file-pdf mr-1"></i>Export Data
        </a>
      </div>
    </div>
  </form>

  @if (Auth::user()->role === RoleType::PIMPINAN)
    <div class="row mb-4">
      <div class="col-lg-6">
        <div class="stat-card">
          <div class="stat-icon" style="background:#fff3cd;"><i class="fas fa-coins" style="color:#b8860b;"></i></div>
          <div>
            <div class="stat-label">Pendapatan</div>
            <div class="stat-value">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
            <div class="stat-trend {{ $revenueGrowth >= 0 ? 'trend-up' : 'trend-down' }}">
              <i class="fas fa-arrow-{{ $revenueGrowth >= 0 ? 'up' : 'down' }} mr-1"></i>
              {{ abs($revenueGrowth) }}% vs periode lalu
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e8f5e9;">
            <i class="fas fa-calculator" style="color:#2e7d32;"></i>
          </div>
          <div>
            <div class="stat-label">Rata-rata Order</div>
            <div class="stat-value">Rp{{ number_format($averageOrderValue, 0, ',', '.') }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:#f5ebe0;"><i class="fas fa-receipt" style="color:#7b4a1e;"></i></div>
          <div>
            <div class="stat-label">Total Order</div>
            <div class="stat-value">{{ $monthly }}</div>
            <div class="stat-trend {{ $ordersGrowth >= 0 ? 'trend-up' : 'trend-down' }}">
              <i class="fas fa-arrow-{{ $ordersGrowth >= 0 ? 'up' : 'down' }} mr-1"></i>
              {{ abs($ordersGrowth) }}% vs periode lalu
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:#f3e5f5;"><i class="fas fa-cash-register" style="color:#6a1b9a;"></i>
          </div>
          <div>
            <div class="stat-label">Order Kasir</div>
            <div class="stat-value">{{ $normalOrder }}</div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e3f2fd;"><i class="fas fa-qrcode" style="color:#1565c0;"></i></div>
          <div>
            <div class="stat-label">Order QR Self</div>
            <div class="stat-value">{{ $selfOrder }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-4">
        <div class="card" style="border-radius:14px; overflow:hidden;">
          <div class="table-card-header">
            <h5><i class="fas fa-chart-bar mr-2"></i>Pendapatan Harian</h5>
          </div>
          <div class="card-body" style="height: 18rem;">
            @if ($daily->count() > 0)
              <canvas id="chartRevenue"></canvas>
            @else
              <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>Tidak ada pesanan di periode ini.
              </div>
            @endif
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card" style="border-radius:14px; overflow:hidden;">
          <div class="table-card-header">
            <h5><i class="fas fa-chart-line mr-2"></i>Jumlah Order Harian</h5>
          </div>
          <div class="card-body" style="height: 18rem;">
            @if ($daily->count() > 0)
              <canvas id="chartOrders"></canvas>
            @else
              <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>Tidak ada pesanan di periode ini.
              </div>
            @endif
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card" style="border-radius:14px; overflow:hidden;">
          <div class="table-card-header">
            <h5><i class="fas fa-credit-card mr-2"></i>Metode Pembayaran</h5>
          </div>
          <div class="card-body" style="height: 18rem; display:flex; justify-content:center;">
            @if ($paymentBreakdown->count() > 0)
              <canvas id="chartPayment"></canvas>
            @else
              <div class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>Tidak ada data.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-6">
        <div class="card" style="border-radius:14px; overflow:hidden;">
          <div class="table-card-header">
            <h5 class="card-title">
              <i class="fas fa-trophy mr-2"></i> Produk Terlaris
            </h5>
          </div>
          <div class="card-body p-0">
            @forelse ($bestSellers as $i => $item)
              <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5ebe0;">
                <div class="mr-3"
                  style="background:#f5ebe0; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <i class="fas fa-utensils" style="color:#7b4a1e;"></i>
                </div>
                <div style="flex:1;">
                  <div style="font-weight:700; color:#2d1200; font-size:.88rem;">{{ $item->menu?->name }}</div>
                  <div style="font-size:.72rem; color:#aaa;">{{ $item->menu?->category?->label() }}</div>
                </div>
                <div style="text-align:right;">
                  <div style="font-weight:700; color:#4e2a04; font-size:.85rem;">{{ $item->total_qty }} terjual</div>
                  <div style="font-size:.72rem; color:#aaa;">Rp{{ number_format($item->total_revenue, 0, ',', '.') }}
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Tidak ada data.
              </div>
            @endforelse
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card" style="border-radius:14px; overflow:hidden;">
          <div class="table-card-header">
            <h5 class="card-title">
              <i class="fas fa-arrow-down mr-2"></i> Produk Penjualan Terendah
            </h5>
          </div>
          <div class="card-body p-0">
            @forelse ($worstSellers as $item)
              <div class="d-flex align-items-center px-3 py-2" style="border-bottom:1px solid #f5ebe0;">
                <div class="mr-3"
                  style="background:#fbe9e7; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <i class="fas fa-utensils" style="color:#bf360c;"></i>
                </div>
                <div style="flex:1;">
                  <div style="font-weight:700; color:#2d1200; font-size:.88rem;">{{ $item->menu?->name }}</div>
                  <div style="font-size:.72rem; color:#aaa;">{{ $item->menu?->category?->label() }}</div>
                </div>
                <div style="text-align:right;">
                  <div style="font-weight:700; color:#e53935; font-size:.85rem;">{{ $item->total_qty }} terjual</div>
                  <div style="font-size:.72rem; color:#aaa;">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Tidak ada data.
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  @endif

  <div class="table-card mb-4">
    <div class="table-card-header">
      <h5><i class="fas fa-list mr-2"></i>Riwayat Pesanan</h5>
    </div>
    <table class="table table-hover tbl mb-0">
      <thead>
        <tr>
          <th>Invoice</th>
          <th>Tipe</th>
          <th>Meja</th>
          <th>Total</th>
          <th>Bayar</th>
          <th>Status</th>
          <th>Waktu</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($orders as $order)
          <tr>
            <td><code style="color:#7b4a1e; font-size:.8rem;">{{ $order->invoice }}</code></td>
            <td><span class="{{ $order->type->style() }}">{{ $order->type->label() }}</span></td>
            <td>{{ $order->table?->number }}</td>
            <td style="font-weight:700; color:#d4a843;">Rp
              {{ number_format($order->grand_total, 0, ',', '.') }}</td>
            <td><small>{{ $order->payment?->method->label() }}</small></td>
            <td>
              <span class="{{ $order->status->value === 'selesai' ? 'badge-selesai' : 'badge-pending' }}">
                {{ $order->status->label() }}
              </span>
            </td>
            <td style="font-size:.8rem; color:#888;">{{ $order->created_at->format('d/m H:i') }}</td>
            <td>
              <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" class="btn-nota">
                <i class="fas fa-print mr-1"></i>Nota
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="fas fa-inbox fa-3x mb-3 d-block"></i>Tidak ada pesanan yang sesuai filter.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-3 pt-3">
      {{ $orders->links() }}
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('plugins/chart.js/Chart.bundle.min.js') }}"></script>
  @if (Auth::user()->role === RoleType::PIMPINAN)
    <script>
      const labels = {!! json_encode($daily->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M'))) !!};
      const revenue = {!! json_encode($daily->pluck('total')) !!};
      const counts = {!! json_encode($daily->pluck('count')) !!};

      const paymentLabels = {!! json_encode($paymentBreakdown->keys()->map(fn($k) => \App\Enums\PaymentMethod::from($k)->label())) !!};
      const paymentData = {!! json_encode($paymentBreakdown->values()) !!};

      const sharedOptions = {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
      };

      new Chart(document.getElementById('chartRevenue'), {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Pendapatan (Rp)',
            data: revenue,
            backgroundColor: 'rgba(123,74,30,.7)',
            borderColor: '#4e2a04',
            borderWidth: 2,
            borderRadius: 8,
          }]
        },
        options: {
          ...sharedOptions,
          plugins: {
            ...sharedOptions.plugins,
            tooltip: {
              callbacks: {
                label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
              }
            }
          },
          scales: {
            y: {
              ticks: {
                callback: v => 'Rp ' + v.toLocaleString('id-ID')
              }
            }
          }
        }
      });

      new Chart(document.getElementById('chartOrders'), {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Jumlah Order',
            data: counts,
            backgroundColor: 'rgba(21,101,192,.15)',
            borderColor: '#1565c0',
            borderWidth: 2,
            pointBackgroundColor: '#1565c0',
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          ...sharedOptions,
          plugins: {
            ...sharedOptions.plugins,
            tooltip: {
              callbacks: {
                label: ctx => ctx.parsed.y + ' order'
              }
            }
          },
          scales: {
            y: {
              ticks: {
                stepSize: 1
              },
              beginAtZero: true
            }
          }
        }
      });

      new Chart(document.getElementById('chartPayment'), {
        type: 'doughnut',
        data: {
          labels: paymentLabels,
          datasets: [{
            data: paymentData,
            backgroundColor: [
              'rgba(123,74,30,.8)',
              'rgba(21,101,192,.8)',
              'rgba(46,125,50,.8)'
            ],
            borderWidth: 2,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: ctx => ctx.label + ': ' + ctx.parsed + ' transaksi'
              }
            }
          }
        }
      });
    </script>
  @endif
@endpush
