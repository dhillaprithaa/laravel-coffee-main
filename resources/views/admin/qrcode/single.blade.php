<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Meja {{ $table->number }} — {{ config('app.name') }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap"
    rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #f0ebe4;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .preview-hint {
      background: rgba(78, 42, 4, .08);
      border: 1px solid rgba(78, 42, 4, .15);
      border-radius: 10px;
      padding: 10px 20px;
      color: #7b4a1e;
      font-size: .82rem;
      font-weight: 600;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .qr-card {
      width: 320px;
      background: white;
      border-radius: 28px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, .15), 0 4px 12px rgba(78, 42, 4, .1);
      overflow: hidden;
      position: relative;
    }

    .card-top {
      background: linear-gradient(135deg, #1a0a00 0%, #4e2a04 60%, #7b4a1e 100%);
      padding: 28px 28px 60px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .card-top::before {
      content: '';
      position: absolute;
      width: 200px;
      height: 200px;
      background: rgba(212, 168, 67, .08);
      border-radius: 50%;
      top: -80px;
      right: -60px;
    }

    .card-top::after {
      content: '';
      position: absolute;
      width: 140px;
      height: 140px;
      background: rgba(212, 168, 67, .06);
      border-radius: 50%;
      bottom: -40px;
      left: -30px;
    }

    .brand-logo {
      font-size: 2rem;
      margin-bottom: 6px;
    }

    .brand-name {
      color: #d4a843;
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: 3px;
      text-transform: uppercase;
      opacity: .9;
    }

    .brand-tagline {
      color: rgba(245, 235, 224, .5);
      font-size: .65rem;
      letter-spacing: 1px;
      margin-top: 2px;
    }

    .qr-box-wrap {
      display: flex;
      justify-content: center;
      margin-top: -44px;
      position: relative;
      z-index: 2;
    }

    .qr-box {
      background: white;
      border-radius: 20px;
      padding: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    #qrcode canvas,
    #qrcode img {
      border-radius: 8px;
      display: block;
    }

    .card-bottom {
      padding: 20px 28px 28px;
      text-align: center;
    }

    .table-label {
      font-size: .65rem;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: #c8a97a;
      margin-bottom: 4px;
    }

    .table-number {
      font-size: 2.6rem;
      font-weight: 900;
      color: #1a0a00;
      line-height: 1;
      letter-spacing: -1px;
    }

    .divider {
      width: 40px;
      height: 3px;
      background: linear-gradient(90deg, #4e2a04, #d4a843);
      border-radius: 3px;
      margin: 12px auto;
    }

    .scan-instruction {
      font-size: .78rem;
      color: #888;
      font-weight: 500;
      line-height: 1.6;
    }

    .scan-instruction strong {
      color: #4e2a04;
    }

    .steps {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 16px;
    }

    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      font-size: .65rem;
      color: #aaa;
      font-weight: 600;
      width: 60px;
    }

    .step-icon {
      width: 32px;
      height: 32px;
      background: #f5ebe0;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .9rem;
      color: #7b4a1e;
    }

    .card-footer-bar {
      background: linear-gradient(135deg, #f5ebe0, #eedcca);
      padding: 10px 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .footer-url {
      font-size: .6rem;
      color: #aaa;
      font-family: monospace;
      letter-spacing: .3px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 200px;
    }

    .footer-wifi {
      font-size: .65rem;
      color: #c8a97a;
      font-weight: 700;
    }

    .actions {
      margin-top: 28px;
      display: flex;
      gap: 12px;
    }

    .btn-print {
      background: linear-gradient(135deg, #4e2a04, #7b4a1e);
      color: white;
      border: none;
      border-radius: 14px;
      padding: 12px 28px;
      font-size: .9rem;
      font-weight: 700;
      cursor: pointer;
      transition: all .2s;
      font-family: 'Poppins', sans-serif;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-print:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(78, 42, 4, .3);
    }

    .btn-back {
      background: white;
      color: #4e2a04;
      border: 2px solid #e0d5c8;
      border-radius: 14px;
      padding: 12px 24px;
      font-size: .9rem;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all .2s;
    }

    .btn-back:hover {
      background: #f5ebe0;
      border-color: #c8a97a;
      color: #4e2a04;
    }

    @media print {
      body {
        background: white;
        padding: 0;
        justify-content: flex-start;
      }

      .preview-hint,
      .actions {
        display: none !important;
      }

      .qr-card {
        box-shadow: none;
        border: 1px solid #eee;
        margin: 0;
        page-break-inside: avoid;
      }
    }
  </style>
</head>

<body>
  <div class="preview-hint">
    <span>👁</span>
    Preview — Klik "Cetak QR" untuk mencetak
  </div>
  <div class="qr-card">
    <div class="card-top">
      <div class="brand-logo">☕</div>
      <div class="brand-name">{{ config('app.name') }}</div>
      <div class="brand-tagline">Scan & Order — Self Service</div>
    </div>
    <div class="qr-box-wrap">
      <div class="qr-box">
        <div id="qrcode"></div>
      </div>
    </div>
    <div class="card-bottom">
      <div class="table-label">Nomor Meja</div>
      <div class="table-number">{{ $table->number }}</div>
      <div class="divider"></div>
      <p class="scan-instruction">
        Arahkan kamera HP ke QR Code di atas<br>
        dan mulai pesan <strong>tanpa antri</strong> 🎉
      </p>
      <div class="steps">
        <div class="step">
          <div class="step-icon">📷</div>
          Scan QR
        </div>
        <div class="step">
          <div class="step-icon">🛒</div>
          Pilih Menu
        </div>
        <div class="step">
          <div class="step-icon">✅</div>
          Pesan!
        </div>
      </div>
    </div>
    <div class="card-footer-bar">
      <span class="footer-url">{{ route('selforder.show', $table->number) }}</span>
      <span class="footer-wifi">📶 WiFi gratis!</span>
    </div>
  </div>
  <div class="actions">
    <button class="btn-print" onclick="window.print()">
      🖨️ Cetak QR
    </button>
    <a href="{{ route('admin.tables.index') }}" class="btn-back">
      ← Kembali
    </a>
  </div>

  <script>
    new QRCode(document.getElementById("qrcode"), {
      text: "{{ route('selforder.show', $table->number) }}",
      width: 180,
      height: 180,
      colorDark: "#1a0a00",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
  </script>
</body>

</html>
