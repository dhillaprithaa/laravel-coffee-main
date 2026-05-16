<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Penjualan - {{ $bulan }}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      color: #333;
      line-height: 1.4;
      margin: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
    }

    .header h1 {
      text-transform: uppercase;
      margin: 0;
      font-size: 22px;
      letter-spacing: 1px;
    }

    .header p {
      color: #666;
      margin: 5px 0 0;
      font-size: 14px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
    }

    th {
      background-color: #f8f9fa;
      font-weight: bold;
      text-transform: uppercase;
      color: #444;
    }

    th,
    td {
      border: 1px solid #dee2e6;
      padding: 10px 8px;
      text-align: left;
    }

    .text-right {
      text-align: right;
    }

    .font-mono {
      font-family: "Courier New", Courier, monospace;
    }

    .bg-stripe {
      background-color: #fafafa;
    }

    .footer {
      margin-top: 40px;
      text-align: right;
      font-size: 10px;
      color: #999;
      border-top: 1px solid #eee;
      padding-top: 10px;
    }

    .status-cell {
      text-transform: uppercase;
      font-weight: bold;
      font-size: 9px;
    }

    @media print {
      thead {
        display: table-header-group;
      }

      tr {
        page-break-inside: avoid;
      }
    }
  </style>
</head>

<body>

  <div class="header">
    <h1>Laporan Penjualan</h1>
    <p>Periode: {{ $bulan }}</p>
  </div>

  <table>
    <thead>
      <tr>
        <th>Invoice</th>
        <th>Tipe</th>
        <th>Meja</th>
        <th>Pelanggan</th>
        <th>Kasir</th>
        <th class="text-right">Total</th>
        <th>Status</th>
        <th>Waktu</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($orders as $index => $order)
        <tr class="{{ $index % 2 === 1 ? 'bg-stripe' : '' }}">
          <td class="font-mono">{{ $order->invoice }}</td>
          <td>{{ $order->type?->label() }}</td>
          <td>{{ $order->table?->number }}</td>
          <td>{{ $order->nama_pelanggan }}</td>
          <td>{{ $order->user?->name }}</td>
          <td class="text-right">
            {{ number_format($order->grand_total, 0, ',', '.') }}
          </td>
          <td class="status-cell">{{ $order->status?->label() }}</td>
          <td style="white-space: nowrap;">
            {{ $order->created_at->format('d/m/Y H:i') }}
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">
    Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
  </div>

</body>

</html>
