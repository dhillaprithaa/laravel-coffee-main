@php
  use App\Enums\OrderType;
  use App\Enums\PaymentStatus;
  use App\Enums\PaymentMethod;
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="theme-color" content="#1a0a00">
  <title>Pesanan Berhasil — {{ config('app.name') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --coffee-dark: #1a0a00;
      --coffee-brown: #4e2a04;
      --coffee-medium: #7b4a1e;
      --coffee-light: #c8a97a;
      --coffee-cream: #f5ebe0;
      --accent-gold: #d4a843;
    }

    html,
    body {
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(160deg, var(--coffee-dark) 0%, #2d1200 40%, #3d1800 100%);
      color: white;
      -webkit-font-smoothing: antialiased;
    }

    .page {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 40px 20px 60px;
    }

    .success-ring {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 4px solid rgba(212, 168, 67, .3);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      margin-bottom: 24px;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(212, 168, 67, .3);
      }

      50% {
        box-shadow: 0 0 0 20px rgba(212, 168, 67, .0);
      }
    }

    .success-ring::before {
      content: '';
      position: absolute;
      inset: 8px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(212, 168, 67, .2), rgba(212, 168, 67, .1));
      border: 3px solid rgba(212, 168, 67, .5);
    }

    .success-icon {
      width: 68px;
      height: 68px;
      background: linear-gradient(135deg, #2e7d32, #388e3c);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      position: relative;
      z-index: 1;
      animation: popIn .5s cubic-bezier(.34, 1.56, .64, 1) .2s both;
    }

    @keyframes popIn {
      from {
        transform: scale(0);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .page-title {
      font-size: 1.5rem;
      font-weight: 800;
      text-align: center;
      margin-bottom: 6px;
      animation: fadeUp .4s ease .3s both;
    }

    .page-subtitle {
      font-size: .88rem;
      color: rgba(255, 255, 255, .65);
      text-align: center;
      margin-bottom: 32px;
      animation: fadeUp .4s ease .4s both;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(16px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .invoice-card {
      width: 100%;
      max-width: 420px;
      background: rgba(255, 255, 255, .06);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(212, 168, 67, .2);
      border-radius: 24px;
      padding: 24px;
      margin-bottom: 16px;
      animation: fadeUp .4s ease .5s both;
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .invoice-label {
      font-size: .75rem;
      color: rgba(255, 255, 255, .5);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .invoice-code {
      font-size: .9rem;
      font-weight: 800;
      color: var(--accent-gold);
      font-family: monospace;
      letter-spacing: 1px;
    }

    .table-info {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(212, 168, 67, .1);
      border: 1px solid rgba(212, 168, 67, .2);
      border-radius: 14px;
      padding: 10px 16px;
      margin-bottom: 20px;
    }

    .table-info i {
      color: var(--accent-gold);
      font-size: 1.1rem;
    }

    .table-info span {
      font-size: .9rem;
      font-weight: 600;
    }

    .order-items {
      margin-bottom: 16px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .item-emoji {
      font-size: 1.3rem;
      margin-right: 10px;
    }

    .item-name {
      font-size: .88rem;
      font-weight: 600;
    }

    .item-qty {
      font-size: .75rem;
      color: rgba(255, 255, 255, .5);
    }

    .item-price {
      font-size: .9rem;
      font-weight: 700;
      color: var(--accent-gold);
    }

    .divider {
      height: 1px;
      background: rgba(255, 255, 255, .1);
      margin: 12px 0;
      background-image: repeating-linear-gradient(90deg, rgba(255, 255, 255, .15) 0px, rgba(255, 255, 255, .15) 6px, transparent 6px, transparent 12px);
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .total-label {
      font-size: .9rem;
      font-weight: 600;
      color: rgba(255, 255, 255, .7);
    }

    .total-amount {
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--accent-gold);
    }

    .status-card {
      width: 100%;
      max-width: 420px;
      background: rgba(255, 255, 255, .06);
      border: 1px solid rgba(255, 255, 255, .1);
      border-radius: 24px;
      padding: 20px 24px;
      margin-bottom: 16px;
      animation: fadeUp .4s ease .6s both;
    }

    .status-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .status-row:last-child {
      margin-bottom: 0;
    }

    .status-key {
      font-size: .8rem;
      color: rgba(255, 255, 255, .5);
    }

    .status-val {
      font-size: .85rem;
      font-weight: 700;
    }

    .badge-pending {
      background: rgba(255, 193, 7, .2);
      color: #ffd54f;
      border: 1px solid rgba(255, 193, 7, .3);
      border-radius: 8px;
      padding: 3px 10px;
      font-size: .75rem;
      font-weight: 700;
    }

    .badge-paid {
      background: rgba(76, 175, 80, .2);
      color: #81c784;
      border: 1px solid rgba(76, 175, 80, .3);
      border-radius: 8px;
      padding: 3px 10px;
      font-size: .75rem;
      font-weight: 700;
    }

    .badge-cash {
      background: rgba(255, 255, 255, .1);
      color: rgba(255, 255, 255, .8);
      border-radius: 8px;
      padding: 3px 10px;
      font-size: .75rem;
      font-weight: 600;
    }

    .badge-midtrans {
      background: rgba(33, 150, 243, .15);
      color: #64b5f6;
      border-radius: 8px;
      padding: 3px 10px;
      font-size: .75rem;
      font-weight: 600;
    }

    .info-box {
      width: 100%;
      max-width: 420px;
      background: rgba(212, 168, 67, .08);
      border: 1px solid rgba(212, 168, 67, .2);
      border-radius: 18px;
      padding: 16px 20px;
      margin-bottom: 24px;
      animation: fadeUp .4s ease .7s both;
    }

    .info-box-title {
      font-size: .85rem;
      font-weight: 700;
      color: var(--accent-gold);
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .info-box-text {
      font-size: .8rem;
      color: rgba(255, 255, 255, .65);
      line-height: 1.6;
    }

    .btn-wrap {
      width: 100%;
      max-width: 420px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      animation: fadeUp .4s ease .8s both;
    }

    .btn-back {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--coffee-brown), var(--coffee-medium));
      color: white;
      border: none;
      border-radius: 16px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      text-align: center;
      display: block;
      transition: all .2s;
    }

    .btn-back:active {
      transform: scale(.98);
      opacity: .9;
      color: white;
    }

    .btn-ghost {
      width: 100%;
      padding: 13px;
      background: rgba(255, 255, 255, .07);
      color: rgba(255, 255, 255, .7);
      border: 1px solid rgba(255, 255, 255, .15);
      border-radius: 16px;
      font-family: 'Poppins', sans-serif;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      text-align: center;
      display: block;
    }

    .countdown-bar {
      width: 100%;
      max-width: 420px;
      margin-bottom: 20px;
      animation: fadeUp .4s ease .55s both;
    }

    .countdown-label {
      font-size: .75rem;
      color: rgba(255, 255, 255, .5);
      margin-bottom: 6px;
      text-align: center;
    }

    .countdown-track {
      height: 5px;
      background: rgba(255, 255, 255, .1);
      border-radius: 5px;
      overflow: hidden;
    }

    .countdown-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--accent-gold), #f0c040);
      border-radius: 5px;
      width: 100%;
      transition: width 1s linear;
    }

    .deco {
      position: fixed;
      opacity: .04;
      pointer-events: none;
      font-size: 6rem;
    }

    .deco-1 {
      top: 10%;
      right: -20px;
      transform: rotate(20deg);
    }

    .deco-2 {
      bottom: 20%;
      left: -20px;
      transform: rotate(-15deg);
    }
  </style>
</head>

<body>
  <div class="deco deco-1">☕</div>
  <div class="deco deco-2">🍃</div>
  <div class="page">
    <div class="success-ring">
      <div class="success-icon">
        <i class="fas fa-check"></i>
      </div>
    </div>
    <h1 class="page-title">Pesanan Diterima! 🎉</h1>
    <p class="page-subtitle">
      Terima kasih! Tim kami sedang menyiapkan pesananmu.
    </p>
    @if ($order->customer)
      <div
        style="
            background: rgba(212,168,67,.15);
            border: 2px solid rgba(212,168,67,.4);
            border-radius: 50px;
            padding: 10px 28px;
            margin-bottom: 24px;
            text-align: center;
            animation: fadeUp .4s ease .45s both;">
        <div
          style="font-size:.7rem; color:rgba(212,168,67,.7); letter-spacing:2px; text-transform:uppercase; margin-bottom:2px;">
          Atas Nama</div>
        <div style="font-size:1.3rem; font-weight:800; color:#d4a843; letter-spacing:1px;">
          {{ strtoupper($order->customer) }}
        </div>
      </div>
    @endif
    <div class="invoice-card">
      <div class="invoice-header">
        <div>
          <div class="invoice-label">Nomor Invoice</div>
          <div class="invoice-code">{{ $order->invoice }}</div>
        </div>
        <div style="text-align:right;">
          <div class="invoice-label">Waktu Pesan</div>
          <div style="font-size:.82rem; font-weight:600; color:rgba(255,255,255,.7);">
            {{ $order->created_at->format('H:i') }}
          </div>
        </div>
      </div>
      <div class="table-info">
        <i class="fas fa-chair"></i>
        @if ($order->table)
          <span>Meja {{ $order->table->number }}</span>
          <span>&nbsp;·</span>
        @endif

        <span style="color: rgba(255,255,255,.5); font-size:.82rem;">
          {{ $order->items->sum('qty') }} item dipesan
        </span>
      </div>
      <div class="order-items">
        @foreach ($order->items as $item)
          <div class="order-item">
            <div style="display:flex; align-items:center;">
              <span class="item-emoji">
                {{ $item->menu->category->emoji() }}
              </span>
              <div>
                <div class="item-name">{{ $item->menu->name }}</div>
                <div class="item-qty">{{ $item->qty }}x · Rp {{ number_format($item->price, 0, ',', '.') }}
                </div>
              </div>
            </div>
            <div class="item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
          </div>
        @endforeach
      </div>
      <div class="divider"></div>
      <div class="total-row">
        <span class="total-label">Total Pembayaran</span>
        <span class="total-amount">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
      </div>
    </div>
    <div class="status-card">
      <div class="status-row">
        <span class="status-key">Status Pesanan</span>
        <span class="badge-pending">⏳ Sedang Diproses</span>
      </div>
      <div class="status-row">
        <span class="status-key">Metode Bayar</span>
        @if ($order->payment && $order->payment->method === PaymentMethod::MIDTRANS)
          <span class="badge-midtrans">💳 Midtrans</span>
        @else
          <span class="badge-cash">💵 Cash</span>
        @endif
      </div>
      <div class="status-row">
        <span class="status-key">Status Bayar</span>
        @if ($order->payment && $order->payment->status === PaymentStatus::PAID)
          <span class="badge-paid">✅ Lunas</span>
        @else
          <span class="badge-pending">⏳ Belum Bayar</span>
        @endif
      </div>
      <div class="status-row">
        <span class="status-key">Tipe Order</span>
        <span class="status-val" style="color:rgba(255,255,255,.8);">QR Self-Order</span>
      </div>
    </div>
    @if (!$order->payment || $order->payment->status !== PaymentStatus::PAID)
      <div class="info-box">
        <div class="info-box-title">
          <i class="fas fa-info-circle"></i>
          Informasi Pembayaran
        </div>
        <div class="info-box-text">
          @if ($order->payment && $order->payment->method === PaymentMethod::CASH)
            Silakan tunggu pesananmu jadi, lalu lakukan pembayaran tunai ke kasir. Sebutkan nomor invoice: <strong
              style="color:var(--accent-gold);">{{ $order->invoice }}</strong>
          @else
            Pembayaran melalui Midtrans sedang diproses. Simpan nomor invoice: <strong
              style="color:var(--accent-gold);">{{ $order->invoice }}</strong>
          @endif
        </div>
      </div>
    @else
      <div class="info-box" style="border-color:rgba(76,175,80,.3); background:rgba(76,175,80,.08);">
        <div class="info-box-title" style="color:#81c784;">
          <i class="fas fa-check-circle"></i>
          Pembayaran Lunas!
        </div>
        <div class="info-box-text">
          Pembayaran berhasil dikonfirmasi. Pesananmu sedang disiapkan. Terima kasih! 🙏
        </div>
      </div>
    @endif
    <div class="btn-wrap">
      @if ($order->table)
        <a href="{{ route('selforder.show', $order->table->id) }}" class="btn-back">
          <i class="fas fa-plus-circle" style="margin-right:8px;"></i>
          Pesan Lagi
        </a>
      @endif
    </div>
    <div style="margin-top:30px; text-align:center; color:rgba(255,255,255,.3); font-size:.75rem;">
      <i class="fas fa-coffee" style="margin-right:5px;"></i>
      {{ config('app.name') }} &copy; {{ date('Y') }}
    </div>
  </div>
  <script>
    function launchConfetti() {
      const emojis = ['☕', '✨', '🎉', '💛', '🍃', '🌟'];
      const count = 15;
      for (let i = 0; i < count; i++) {
        setTimeout(() => {
          const el = document.createElement('div');
          el.style.cssText = `
                        position: fixed;
                        top: -60px;
                        left: ${Math.random() * 100}%;
                        font-size: ${Math.random() * 20 + 16}px;
                        z-index: 9999;
                        pointer-events: none;
                        animation: fall ${Math.random() * 2 + 2}s ease-in forwards;
                        opacity: ${Math.random() * 0.6 + 0.4};
                    `;
          el.textContent = emojis[Math.floor(Math.random() * emojis.length)];
          document.body.appendChild(el);
          setTimeout(() => el.remove(), 4000);
        }, i * 150);
      }
    }
    const style = document.createElement('style');
    style.textContent = `
            @keyframes fall {
                to { top: 110vh; transform: rotate(${Math.random() * 360}deg); }
            }
        `;
    document.head.appendChild(style);
    window.addEventListener('load', () => setTimeout(launchConfetti, 300));
  </script>
</body>

</html>
