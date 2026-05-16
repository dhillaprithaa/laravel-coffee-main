<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nota {{ $order->invoice }} — {{ config('app.name') }}</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      background: #f0f0f0;
      display: flex;
      justify-content: center;
      padding: 30px 0;
    }

    .nota-wrap {
      background: white;
      width: 80mm;
      padding: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
    }

    .text-center {
      text-align: center;
    }

    .bold {
      font-weight: bold;
    }

    .divider {
      border-top: 1px dashed #aaa;
      margin: 8px 0;
    }

    .header h2 {
      font-size: 15px;
      font-weight: bold;
      letter-spacing: 2px;
    }

    .header small {
      font-size: 10px;
      color: #555;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin-bottom: 3px;
    }

    .info-row .label {
      color: #555;
    }

    .item-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 4px;
      font-size: 12px;
    }

    .item-row .name {
      flex: 1;
    }

    .item-row .qty {
      width: 30px;
      text-align: center;
    }

    .item-row .sub {
      width: 70px;
      text-align: right;
      font-weight: bold;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      font-weight: bold;
      padding-top: 4px;
    }

    .total-row .val {
      font-size: 14px;
    }

    .footer-text {
      text-align: center;
      font-size: 11px;
      color: #777;
      margin-top: 8px;
      line-height: 1.6;
    }

    .actions {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin-top: 20px;
    }

    .btn-print {
      background: #4e2a04;
      color: white;
      border: none;
      padding: 10px 28px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-back {
      background: #f5ebe0;
      color: #4e2a04;
      border: none;
      padding: 10px 28px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
    }

    @media print {
      body {
        background: white;
        padding: 0;
      }

      .nota-wrap {
        box-shadow: none;
        width: 100%;
      }

      .actions {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div>
    <div class="nota-wrap" id="printArea">
      <div class="header text-center" style="margin-bottom:10px;">
        <h2>☕ {{ config('app.name') }}</h2>
        <small>Jl. Kopi Manis No.1, Kota Anda</small><br>
        <small>Telp. 08xx-xxxx-xxxx</small>
      </div>
      <div class="divider"></div>
      <div class="info-row"><span class="label">Invoice</span><span class="bold">{{ $order->invoice }}</span></div>
      <div class="info-row"><span
          class="label">Tanggal</span><span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
      <div class="info-row"><span class="label">Kasir</span><span>{{ $order->user?->name ?? 'Self-Order' }}</span>
      </div>
      <div class="info-row"><span
          class="label">Tipe</span><span>{{ $order->type === 'kasir' ? 'Kasir / POS' : 'QR Self-Order' }}</span>
      </div>
      @if ($order->table)
        <div class="info-row"><span class="label">Meja</span><span>Meja {{ $order->table->number }}</span></div>
      @endif
      <div class="info-row">
        <span class="label">Bayar</span>
        <span>{{ $order->payment ? $order->payment->method->label() : '-' }}</span>
      </div>
      @if ($order->customer)
        <div class="divider"></div>
        <div style="text-align:center; margin: 6px 0;">
          <div style="font-size:10px; color:#888; letter-spacing:1px; text-transform:uppercase;">Atas Nama</div>
          <div style="font-size:16px; font-weight:bold; letter-spacing:1px; margin-top:2px;">
            {{ strtoupper($order->customer) }}</div>
        </div>
      @endif
      <div class="divider"></div>
      <div class="item-row bold">
        <span class="name">Menu</span>
        <span class="qty">Qty</span>
        <span class="sub">Subtotal</span>
      </div>
      <div class="divider" style="margin:4px 0;"></div>
      @foreach ($order->items as $item)
        <div class="item-row">
          <span class="name">{{ $item->menu->name }}</span>
          <span class="qty">{{ $item->qty }}x</span>
          <span class="sub">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
      @endforeach
      <div class="divider"></div>
      <div class="total-row">
        <span>TOTAL</span>
        <span class="val">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
      </div>
      <div class="divider"></div>
      <div class="footer-text">
        Terima kasih sudah berkunjung!<br>
        Semoga hari Anda menyenangkan ☕<br>
        <small style="font-size:10px;">Powered by {{ config('app.name') }} POS</small>
      </div>
    </div>
    <div class="actions">
      <button class="btn-print" onclick="window.print()">🖨️ Cetak Nota</button>
      <a href="{{ url()->previous() }}" class="btn-back">← Kembali</a>
    </div>
  </div>
</body>

</html>
