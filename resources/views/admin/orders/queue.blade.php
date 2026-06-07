@extends('layouts.admin')
@section('title', 'Antrian Pesanan')
@section('page-title', 'Antrian Pesanan')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/orders/queue.css') }}">
@endpush

@php
  use App\Enums\PaymentStatus;
  use App\Enums\OrderStatus;
  use App\Enums\OrderType;
@endphp

@section('content')
  <div id="toastWrap"></div>
  <div class="queue-header">
    <div>
      <h4><i class="fas fa-list-alt mr-2"></i>Antrian Pesanan</h4>
      <p>Kelola status pembayaran dan pesanan dari semua order (Kasir & QR Self-Order)</p>
    </div>
    <div class="queue-stats">
      <div class="stat-pill pending">
        <i class="fas fa-hourglass-half"></i>
        <span id="countPending">{{ $ongoing->where('status', 'pending')->count() }}</span> Pending
      </div>
      <div class="stat-pill diproses">
        <i class="fas fa-blender"></i>
        <span id="countDiproses">{{ $ongoing->where('status', 'diproses')->count() }}</span> Diproses
      </div>
      <div class="stat-pill selesai">
        <i class="fas fa-check-double"></i>
        {{ $completed->count() }} Selesai Hari Ini
      </div>
      <div class="refresh-badge">
        <div class="refresh-dot"></div>
        Auto-refresh <span id="countdown">30</span>s
        <div class="countdown-track">
          <div id="countdown-fill" style="width:100%;"></div>
        </div>
      </div>
      <button class="btn btn-coffee btn-sm" onclick="location.reload()">
        <i class="fas fa-sync-alt mr-1"></i>Refresh
      </button>
    </div>
  </div>
  @if ($ongoing->count() > 0)
    <div class="section-title">
      <i class="fas fa-fire" style="color:#e65100;"></i>
      Sedang Aktif — {{ $ongoing->count() }} pesanan
    </div>
    <div id="aktifList">
      @foreach ($ongoing as $order)
        @php
          $isPaid = optional($order->payment)->status === PaymentStatus::PAID;
          $isPending = $order->status === OrderStatus::PENDING;
          $isDiproses = $order->status === OrderStatus::DIPROSES;
        @endphp
        <div class="order-card {{ $order->status }}" id="card-{{ $order->id }}">
          <div class="card-head">
            <div>
              <div class="invoice-row">
                <span class="invoice-code">{{ $order->invoice }}</span>
                <span class="{{ $order->type->style() }}">
                  <i class="{{ $order->type->icon() }} mr-1"></i>{{ $order->type->label() }}
                </span>
                @if ($order->table)
                  <span class="badge-meja"><i class="fas fa-chair mr-1"></i>Meja {{ $order->table->number }}</span>
                @endif
              </div>
              <div class="order-time mt-1">
                <i class="fas fa-clock mr-1"></i>{{ $order->created_at->format('H:i') }}
                <span style="color:#ccc; margin: 0 4px;">·</span>{{ $order->created_at->diffForHumans() }}
                @if ($order->user)
                  <span style="color:#ccc; margin: 0 4px;">·</span>
                  <i class="fas fa-user-tie mr-1"></i>{{ $order->user->name }}
                @endif
              </div>
              @if ($order->customer)
                <div class="pelanggan-info">
                  <i class="fas fa-user mr-1"></i>{{ $order->customer }}
                </div>
              @endif
            </div>
            <div class="status-group">
              <span class="status-badge {{ $isPaid ? 'sb-bayar-paid' : 'sb-bayar-unpaid' }}"
                id="sb-bayar-{{ $order->id }}">
                @if ($isPaid)
                  <i class="fas fa-check-circle"></i> Lunas
                @else
                  <i class="fas fa-times-circle"></i> Belum Bayar
                @endif
              </span>
              <span class="status-badge sb-pesanan-{{ $order->status }}" id="sb-pesanan-{{ $order->id }}">
                @if ($isPending)
                  <i class="fas fa-hourglass-half"></i> Pending
                @elseif($isDiproses)
                  <i class="fas fa-blender"></i> Diproses
                @else
                  <i class="fas fa-check-double"></i> Selesai
                @endif
              </span>
            </div>
          </div>
          <div class="items-section">
            @foreach ($order->items as $item)
              <div class="item-row">
                <span class="item-name">{{ $item->menu->name }}</span>
                <div style="display:flex; align-items:center; gap:8px;">
                  <span class="item-qty">×{{ $item->qty }}</span>
                  <span class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
              </div>
            @endforeach
          </div>
          <div class="card-foot">
            <div class="total-block">
              <div class="total-label">Total</div>
              <div class="total-val">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</div>
            </div>
            <div class="actions">
              <span class="{{ $order->payment->method->style() }}">{{ $order->payment->method->combined() }}</span>
              <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" class="btn-act btn-nota">
                <i class="fas fa-print"></i>Nota
              </a>
              @if (!$isPaid)
                <button class="btn-act btn-lunas" id="btn-lunas-{{ $order->id }}"
                  onclick="konfirmasiBayar('{{ $order->id }}')">
                  <i class="fas fa-money-bill-wave"></i>Lunas
                </button>
              @endif
              @if ($isPaid && $isDiproses)
                <button class="btn-act btn-selesai" id="btn-selesai-{{ $order->id }}"
                  onclick="selesaikanPesanan('{{ $order->id }}', '{{ $order->invoice }}')">
                  <i class="fas fa-check-circle"></i>Selesai
                </button>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="empty-q" id="emptyMsg">
      <i class="fas fa-check-double d-block"></i>
      <h5>Tidak ada pesanan aktif!</h5>
      <p>Semua pesanan hari ini sudah diproses. 🎉</p>
    </div>
  @endif
  @if ($completed->count() > 0)
    <div class="section-title" style="color: #28a745;">
      <i class="fas fa-check-circle" style="color:#28a745;"></i>
      Selesai Hari Ini — {{ $completed->count() }} pesanan
    </div>
    @foreach ($completed as $order)
      <div class="order-card selesai">
        <div class="card-head">
          <div>
            <div class="invoice-row">
              <span class="invoice-code">{{ $order->invoice }}</span>
              <span class="{{ $order->type->style() }}">
                <i class="{{ $order->type->icon() }} mr-1"></i>{{ $order->type->label() }}
              </span>
              @if ($order->table)
                <span class="badge-meja"><i class="fas fa-chair mr-1"></i>Meja {{ $order->table->number }}</span>
              @endif
            </div>
            <div class="order-time mt-1">
              <i class="fas fa-clock mr-1"></i>{{ $order->created_at->format('H:i') }}
            </div>
          </div>
          <div class="status-group">
            <span class="status-badge sb-bayar-paid"><i class="fas fa-check-circle"></i> Lunas</span>
            <span class="status-badge sb-pesanan-selesai"><i class="fas fa-check-double"></i> Selesai</span>
          </div>
        </div>
        <div class="card-foot" style="background:transparent; border-top: 1px solid #f0e8dc;">
          <div class="total-block">
            <span style="font-size:.8rem; color:#888;">Total: </span>
            <strong style="color:#4e2a04; font-size:.95rem;">Rp
              {{ number_format($order->grand_total, 0, ',', '.') }}</strong>
            <span style="color:#ccc; font-size:.75rem; margin-left:6px;">— {{ $order->items->sum('qty') }}
              item</span>
          </div>
          <div class="actions">
            <span class="{{ $order->payment->method->style() }}">{{ $order->payment->method->combined() }}</span>
            <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" class="btn-act btn-nota">
              <i class="fas fa-print"></i>Nota
            </a>
          </div>
        </div>
      </div>
    @endforeach
  @endif
@endsection

@push('scripts')
  <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const BASE = '{{ route('admin.orders.index') }}';
    let countdown = 30;

    function konfirmasiBayar(orderId) {
      if (!confirm('Konfirmasi pembayaran sudah diterima untuk pesanan ini?')) return;
      const btn = document.getElementById('btn-lunas-' + orderId);
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proses...';
      }
      fetch(`${BASE}/${orderId}/complete`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast('💵 ' + data.message, 'success');
            const sbBayar = document.getElementById('sb-bayar-' + orderId);
            if (sbBayar) {
              sbBayar.className = 'status-badge sb-bayar-paid';
              sbBayar.innerHTML = '<i class="fas fa-check-circle"></i> Lunas';
            }
            const sbPesanan = document.getElementById('sb-pesanan-' + orderId);
            if (sbPesanan && data.new_status === 'diproses') {
              sbPesanan.className = 'status-badge sb-pesanan-diproses';
              sbPesanan.innerHTML = '<i class="fas fa-blender"></i> Diproses';
              const card = document.getElementById('card-' + orderId);
              if (card) {
                card.classList.remove('pending');
                card.classList.add('diproses');
              }
            }
            if (btn) btn.remove();
            const actions = document.querySelector('#card-' + orderId + ' .actions');
            if (actions) {
              const btnSelesai = document.createElement('button');
              btnSelesai.className = 'btn-act btn-selesai';
              btnSelesai.id = 'btn-selesai-' + orderId;
              btnSelesai.innerHTML = '<i class="fas fa-check-circle"></i>Selesai';
              btnSelesai.onclick = () => selesaikanPesanan(orderId, '');
              actions.appendChild(btnSelesai);
            }
            updateStatPills();
          } else {
            if (btn) {
              btn.disabled = false;
              btn.innerHTML = '<i class="fas fa-money-bill-wave"></i>Lunas';
            }
            showToast('❌ ' + (data.message || 'Gagal konfirmasi pembayaran.'), 'error');
          }
        })
        .catch(err => {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-money-bill-wave"></i>Lunas';
          }
          showToast('❌ Koneksi bermasalah. Coba refresh.', 'error');
          console.error(err);
        });
    }

    function selesaikanPesanan(orderId, invoice) {
      const label = invoice ? `pesanan ${invoice}` : 'pesanan ini';
      if (!confirm(`Tandai ${label} sebagai SELESAI?`)) return;
      const btn = document.getElementById('btn-selesai-' + orderId);
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proses...';
      }
      fetch(`${BASE}/${orderId}/status`, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            status: 'selesai'
          })
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showToast('✅ ' + data.message, 'success');
            const card = document.getElementById('card-' + orderId);
            if (card) {
              card.style.transition = 'all .4s ease';
              card.style.opacity = '0';
              card.style.transform = 'translateX(40px)';
              setTimeout(() => {
                card.remove();
                updateStatPills();
                checkEmptyState();
              }, 400);
            }
          } else {
            if (btn) {
              btn.disabled = false;
              btn.innerHTML = '<i class="fas fa-check-circle"></i>Selesai';
            }
            showToast('❌ Gagal menyelesaikan pesanan.', 'error');
          }
        })
        .catch(err => {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i>Selesai';
          }
          showToast('❌ Koneksi bermasalah.', 'error');
          console.error(err);
        });
    }

    function updateStatPills() {
      const cards = document.querySelectorAll('#aktifList .order-card');
      let pending = 0,
        diproses = 0;
      cards.forEach(c => {
        if (c.classList.contains('pending')) pending++;
        if (c.classList.contains('diproses')) diproses++;
      });
      const elP = document.getElementById('countPending');
      const elD = document.getElementById('countDiproses');
      if (elP) elP.textContent = pending;
      if (elD) elD.textContent = diproses;
    }

    function checkEmptyState() {
      const cards = document.querySelectorAll('#aktifList .order-card');
      if (cards.length === 0) {
        const list = document.getElementById('aktifList');
        if (list) {
          list.innerHTML = `
                <div class="empty-q">
                    <i class="fas fa-check-double d-block"></i>
                    <h5>Tidak ada pesanan aktif!</h5>
                    <p>Semua pesanan sudah diproses. 🎉</p>
                </div>`;
        }
      }
    }

    function showToast(msg, type = 'info') {
      const wrap = document.getElementById('toastWrap');
      const div = document.createElement('div');
      div.className = 't-toast ' + type;
      div.textContent = msg;
      wrap.appendChild(div);
      setTimeout(() => {
        div.style.opacity = '0';
        div.style.transition = 'opacity .3s';
        setTimeout(() => div.remove(), 300);
      }, 3500);
    }
    let cdInterval = setInterval(() => {
      countdown--;
      const el = document.getElementById('countdown');
      const fill = document.getElementById('countdown-fill');
      if (el) el.textContent = countdown;
      if (fill) fill.style.width = (countdown / 30 * 100) + '%';
      if (countdown <= 0) {
        clearInterval(cdInterval);
        location.reload();
      }
    }, 1000);
  </script>
@endpush
