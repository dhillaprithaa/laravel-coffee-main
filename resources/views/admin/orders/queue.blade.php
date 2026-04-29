@extends('layouts.admin')
@section('title', 'Antrian Pesanan')
@section('page-title', 'Antrian Pesanan')

@push('styles')
  <style>
    .queue-header {
      background: linear-gradient(135deg, #1a0a00 0%, #4e2a04 100%);
      border-radius: 16px;
      padding: 20px 24px;
      color: white;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .queue-header h4 {
      margin: 0;
      font-weight: 700;
      color: #d4a843;
    }

    .queue-header p {
      margin: 0;
      font-size: .85rem;
      color: rgba(245, 235, 224, .7);
    }

    .queue-stats {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }

    .stat-pill {
      border-radius: 50px;
      padding: 5px 14px;
      font-weight: 700;
      font-size: .82rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .stat-pill.pending {
      background: #fff3cd;
      color: #856404;
    }

    .stat-pill.diproses {
      background: #cff4fc;
      color: #055160;
    }

    .stat-pill.selesai {
      background: #d1e7dd;
      color: #0a5436;
    }

    .refresh-badge {
      background: rgba(212, 168, 67, .15);
      border: 1px solid rgba(212, 168, 67, .3);
      color: #d4a843;
      border-radius: 8px;
      padding: 5px 12px;
      font-size: .78rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .refresh-dot {
      width: 8px;
      height: 8px;
      background: #d4a843;
      border-radius: 50%;
      animation: blink 1.5s ease-in-out infinite;
    }

    @keyframes blink {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: .2;
      }
    }

    .section-title {
      font-size: .7rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #aaa;
      margin: 22px 0 10px;
      padding-bottom: 6px;
      border-bottom: 2px solid #eee;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .order-card {
      background: white;
      border-radius: 14px;
      box-shadow: 0 3px 16px rgba(0, 0, 0, .07);
      border-left: 5px solid transparent;
      margin-bottom: 16px;
      transition: transform .15s, box-shadow .15s;
      overflow: hidden;
    }

    .order-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 24px rgba(0, 0, 0, .11);
    }

    .order-card.pending {
      border-left-color: #ffc107;
    }

    .order-card.diproses {
      border-left-color: #0dcaf0;
    }

    .order-card.selesai {
      border-left-color: #28a745;
      opacity: .72;
    }

    .card-head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      padding: 14px 16px 10px;
      flex-wrap: wrap;
      gap: 8px;
    }

    .invoice-row {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .invoice-code {
      font-family: monospace;
      font-size: .83rem;
      font-weight: 800;
      color: #7b4a1e;
      letter-spacing: .5px;
    }

    .badge-tipe-qr {
      background: #e3f2fd;
      color: #1565c0;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: .8rem;
      font-weight: 700;
    }

    .badge-tipe-kasir {
      background: #f3e5f5;
      color: #6a1b9a;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: .8rem;
      font-weight: 700;
    }

    .badge-meja {
      background: linear-gradient(135deg, #4e2a04, #7b4a1e);
      color: white;
      border-radius: 20px;
      padding: 2px 10px;
      font-size: .72rem;
      font-weight: 700;
    }

    .order-time {
      font-size: .76rem;
      color: #aaa;
    }

    .pelanggan-info {
      font-size: .78rem;
      color: #888;
      font-style: italic;
      margin-top: 2px;
    }

    .status-group {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4px;
    }

    .status-badge {
      border-radius: 20px;
      padding: 3px 10px;
      font-size: .72rem;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
    }

    .sb-bayar-unpaid {
      background: #fce8e8;
      color: #c62828;
      border: 1px solid #ef9a9a;
    }

    .sb-bayar-paid {
      background: #e8f5e9;
      color: #1b5e20;
      border: 1px solid #a5d6a7;
    }

    .sb-pesanan-pending {
      background: #fff8e1;
      color: #e65100;
      border: 1px solid #ffcc80;
    }

    .sb-pesanan-diproses {
      background: #e0f7fa;
      color: #006064;
      border: 1px solid #80deea;
    }

    .sb-pesanan-selesai {
      background: #e8f5e9;
      color: #1b5e20;
      border: 1px solid #a5d6a7;
    }

    .items-section {
      padding: 0 16px 10px;
    }

    .item-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      border-bottom: 1px dashed #f0e8dc;
      font-size: .85rem;
    }

    .item-row:last-child {
      border-bottom: none;
    }

    .item-name {
      font-weight: 600;
      color: #2d1200;
    }

    .item-qty {
      background: #f5ebe0;
      color: #7b4a1e;
      border-radius: 20px;
      padding: 1px 9px;
      font-size: .72rem;
      font-weight: 700;
    }

    .item-price {
      font-weight: 700;
      color: #d4a843;
      font-size: .82rem;
    }

    .card-foot {
      background: #fafafa;
      border-top: 1px solid #f0e8dc;
      padding: 10px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
    }

    .total-block .total-label {
      font-size: .75rem;
      color: #888;
    }

    .total-block .total-val {
      font-size: 1.08rem;
      font-weight: 800;
      color: #4e2a04;
    }

    .metode-badge-cash {
      background: #e8f5e9;
      color: #1b5e20;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: .8rem;
      font-weight: 700;
    }

    .metode-badge-midtrans {
      background: #e3f2fd;
      color: #1565c0;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: .8rem;
      font-weight: 700;
    }

    .actions {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      align-items: center;
    }

    .btn-act {
      border: none;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: .8rem;
      font-weight: 700;
      cursor: pointer;
      transition: all .18s;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      text-decoration: none;
    }

    .btn-act:disabled {
      opacity: .45;
      cursor: not-allowed;
    }

    .btn-lunas {
      background: linear-gradient(135deg, #1b5e20, #388e3c);
      color: white;
    }

    .btn-lunas:hover {
      background: linear-gradient(135deg, #2e7d32, #43a047);
      box-shadow: 0 3px 10px rgba(40, 167, 69, .3);
    }

    .btn-proses {
      background: linear-gradient(135deg, #006064, #0097a7);
      color: white;
    }

    .btn-proses:hover {
      background: linear-gradient(135deg, #00838f, #00acc1);
      box-shadow: 0 3px 10px rgba(0, 188, 212, .3);
    }

    .btn-selesai {
      background: linear-gradient(135deg, #4e2a04, #7b4a1e);
      color: white;
    }

    .btn-selesai:hover {
      background: linear-gradient(135deg, #3d1a00, #5d3210);
      box-shadow: 0 3px 10px rgba(78, 42, 4, .35);
    }

    .btn-nota {
      background: #f5ebe0;
      color: #7b4a1e;
    }

    .btn-nota:hover {
      background: #4e2a04;
      color: white;
    }

    .empty-q {
      text-align: center;
      padding: 50px 20px;
      color: #bbb;
    }

    .empty-q i {
      font-size: 3rem;
      margin-bottom: 12px;
      opacity: .3;
    }

    .empty-q h5 {
      font-weight: 700;
      color: #999;
    }

    #toastWrap {
      position: fixed;
      top: 72px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .t-toast {
      border-radius: 10px;
      padding: 10px 16px;
      font-weight: 600;
      font-size: .85rem;
      color: white;
      min-width: 240px;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 18px rgba(0, 0, 0, .2);
      animation: tIn .3s ease;
    }

    .t-toast.success {
      background: #1b5e20;
    }

    .t-toast.error {
      background: #b71c1c;
    }

    .t-toast.info {
      background: #01579b;
    }

    @keyframes tIn {
      from {
        opacity: 0;
        transform: translateX(30px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .countdown-track {
      width: 80px;
      height: 3px;
      background: rgba(255, 255, 255, .15);
      border-radius: 3px;
      overflow: hidden;
    }

    .countdown-fill {
      height: 100%;
      background: #d4a843;
      border-radius: 3px;
      transition: width 1s linear;
    }
  </style>
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
                  onclick="konfirmasiBayar({{ $order->id }})">
                  <i class="fas fa-money-bill-wave"></i>Lunas
                </button>
              @endif
              @if ($isPaid && $isDiproses)
                <button class="btn-act btn-selesai" id="btn-selesai-{{ $order->id }}"
                  onclick="selesaikanPesanan({{ $order->id }}, '{{ $order->invoice }}')">
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
          method: 'POST',
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
