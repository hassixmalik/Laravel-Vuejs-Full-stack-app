<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice #{{ $invoice['id'] }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { margin-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
    thead { background: #f3f4f6; }
    .right { text-align: right; }
  </style>
</head>
<body>
  <h1>Invoice #{{ $invoice['id'] }}</h1>
  <p><strong>Date:</strong> {{ $invoice['date'] ?? '—' }} |
     <strong>Status:</strong> {{ $invoice['status'] }}</p>

  <p><strong>Customer:</strong> {{ $invoice['customer']['name'] }}<br>
     {{ $invoice['customer']['email'] }}<br>
     {{ $invoice['customer']['phone'] }}<br>
     {{ $invoice['customer']['address'] }}</p>

  <table>
    <thead>
      <tr>
        <th>Item</th><th class="right">Qty</th><th class="right">Price</th><th class="right">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($invoice['items'] as $it)
        <tr>
          <td>{{ $it['description'] }}</td>
          <td class="right">{{ $it['quantity'] }}</td>
          <td class="right">{{ number_format($it['price'],3) }}</td>
          <td class="right">{{ number_format($it['total'],3) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <p class="right">
    Subtotal: {{ number_format($invoice['subtotal'],3) }} BD<br>
    Discount: {{ number_format($invoice['discount'],3) }} BD<br>
    VAT: {{ number_format(($invoice['total'] - max($invoice['subtotal'] - $invoice['discount'], 0)),3) }} BD<br>
    <strong>Total: {{ number_format($invoice['total'],3) }} BD</strong>
  </p>
</body>
</html>
