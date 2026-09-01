<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; color: #111827; }
        .container { width: 100%; padding: 30px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .title { font-size: 28px; font-weight: bold; }
        .meta { margin-top: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #d1d5db; padding: 10px; text-align: left; }
        th { background: #f3f4f6; }
        .totals { width: 280px; margin-left: auto; margin-top: 20px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">Invoice</div>
                <div class="meta"># {{ $invoice->invoice_number }}</div>
            </div>
            <div class="meta">
                <div>Issue: {{ $invoice->issue_date }}</div>
                <div>Due: {{ $invoice->due_date }}</div>
                <div>Status: {{ $invoice->status }}</div>
            </div>
        </div>

        <div>
            <strong>Bill to:</strong>
            <div>{{ $invoice->customer->name }}</div>
            <div>{{ $invoice->customer->email }}</div>
            <div>{{ $invoice->customer->phone }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><span>${{ number_format($invoice->subtotal, 2) }}</span></div>
            <div><span>Tax</span><span>${{ number_format($invoice->tax, 2) }}</span></div>
            <div><strong><span>Total</span><span>${{ number_format($invoice->total, 2) }}</span></strong></div>
        </div>
    </div>
</body>
</html>
