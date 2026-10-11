@php
    use App\Support\Money;
    $restaurant = $order->restaurant;
    $isPaid = $order->payment_status === 'paid';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f7f1ed; color: #211a16; }
        .page { width: min(100% - 32px, 900px); margin: 32px auto 64px; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
        .toolbar h1 { margin: 0; font-size: 18px; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        button, a { border: 0; border-radius: 10px; padding: 11px 15px; font: inherit; font-size: 13px; font-weight: 800; text-decoration: none; cursor: pointer; }
        .primary { background: #ed0b1b; color: #fff; } .secondary { background: #fff; color: #5d4f47; border: 1px solid #dfd1ca; }
        .sheet { overflow: hidden; background: #fff; border: 1px solid #e6d9d2; border-radius: 18px; box-shadow: 0 18px 50px rgba(58, 35, 22, .08); }
        .head { display: flex; justify-content: space-between; gap: 24px; padding: 34px; background: #211a16; color: #fff; }
        .brand { display: flex; gap: 14px; align-items: center; } .logo { width: 54px; height: 54px; border-radius: 15px; object-fit: cover; background: #ed0b1b; } .initials { display:grid; place-items:center; width:54px; height:54px; border-radius:15px; background:#ed0b1b; font-weight:900; font-size:20px; }
        .head h2 { margin: 0; font-size: 22px; } .head p { margin: 5px 0 0; color: #d6c5bb; font-size: 12px; line-height: 1.5; }
        .invoice-meta { text-align: right; } .eyebrow { margin: 0 0 7px; color: #ff8690; font-size: 10px; font-weight: 900; letter-spacing: .18em; text-transform: uppercase; } .invoice-number { margin:0; font-size: 20px; font-weight: 900; }
        .status { display:inline-block; margin-top:10px; padding:6px 9px; border-radius:999px; background: {{ $isPaid ? '#dff4d2' : '#fff0c9' }}; color: {{ $isPaid ? '#2c711b' : '#8a5a00' }}; font-size:10px; font-weight:900; text-transform:uppercase; }
        .body { padding: 30px 34px; } .info { display:grid; grid-template-columns: 1fr 1fr; gap: 24px; padding-bottom: 26px; border-bottom:1px solid #eee4df; } .label { margin:0 0 6px; color:#907f75; font-size:10px; font-weight:900; letter-spacing:.14em; text-transform:uppercase; } .value { margin:0; font-size:13px; font-weight:750; line-height:1.55; }
        table { width:100%; border-collapse: collapse; } th { padding: 18px 0 10px; color:#907f75; font-size:10px; text-align:left; text-transform:uppercase; letter-spacing:.12em; } th:last-child, td:last-child { text-align:right; } td { padding: 15px 0; border-top:1px solid #eee4df; vertical-align:top; font-size:13px; } .item-name { font-weight:900; } .muted { margin-top:4px; color:#806f65; font-size:11px; line-height:1.55; }
        .totals { display:flex; justify-content:flex-end; margin-top: 22px; } .totals table { width:min(100%, 330px); } .totals td { border:0; padding:6px 0; color:#806f65; } .totals tr:last-child td { padding-top:13px; border-top:1px solid #e5d8d1; color:#ed0b1b; font-size:18px; font-weight:900; }
        .foot { padding: 18px 34px; background:#fff9f5; color:#806f65; font-size:11px; line-height:1.6; text-align:center; }
        @media (max-width: 620px) { .page { width:min(100% - 20px, 900px); margin: 12px auto 32px; } .toolbar { align-items:flex-start; flex-direction:column; } .head, .body { padding: 22px 18px; } .head { flex-direction:column; } .invoice-meta { text-align:left; } .info { grid-template-columns:1fr; gap:16px; } th:nth-child(2), td:nth-child(2) { display:none; } .foot { padding:16px 18px; } }
        @media print { body { background:#fff; } .page { width:100%; margin:0; } .toolbar { display:none; } .sheet { border:0; box-shadow:none; } }
    </style>
</head>
<body>
<main class="page">
    <div class="toolbar">
        <h1>Order invoice</h1>
        <div class="actions">
            <button class="secondary" onclick="window.print()">Print / Save PDF</button>
            <button class="secondary" onclick="downloadInvoice('png')">Save PNG</button>
            <button class="primary" onclick="downloadInvoice('jpeg')">Save JPEG</button>
        </div>
    </div>

    <article id="invoice-sheet" class="sheet">
        <header class="head">
            <div class="brand">
                @if ($restaurant?->logo_url)<img src="{{ $restaurant->logo_url }}" alt="" class="logo">@else<span class="initials">{{ $restaurant?->initials ?? 'AK' }}</span>@endif
                <div><h2>{{ $restaurant?->name ?? 'Restaurant' }}</h2><p>{{ $restaurant?->formatted_address ?? $restaurant?->address ?? '' }}<br>{{ $restaurant?->phone ?? '' }}{{ $restaurant?->email ? ' · '.$restaurant->email : '' }}</p></div>
            </div>
            <div class="invoice-meta"><p class="eyebrow">Tax invoice</p><p class="invoice-number">{{ $order->order_number }}</p><span class="status">{{ $order->payment_status_label }}</span></div>
        </header>
        <div class="body">
            <section class="info">
                <div><p class="label">Billed to</p><p class="value">{{ $order->customer_name }}<br>{{ $order->customer_phone }}@if($order->customer_email)<br>{{ $order->customer_email }}@endif</p></div>
                <div><p class="label">Order details</p><p class="value">Placed {{ $order->created_at?->format('d M Y, h:i A') }}<br>Payment: {{ $order->payment_method_label }}<br>Delivery: {{ $order->delivery_address }}</p></div>
            </section>
            <table>
                <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
                <tbody>
                @foreach ($order->items as $item)
                    @php $customizations = collect($item->customizations_snapshot ?? []); $legacyAddons = collect($item->addons_snapshot ?? []); @endphp
                    <tr>
                        <td><div class="item-name">{{ $item->item_name }}</div><div class="muted">{{ Money::format($item->price) }} each{{ $item->size_name ? ' · '.$item->size_name : '' }}
                            @if ($customizations->isNotEmpty())<br><strong>Customizations:</strong> {{ $customizations->map(function ($group) { $selected = collect($group['selected'] ?? [])->map(fn ($option) => ($option['name'] ?? 'Option').(((int) ($option['quantity'] ?? 1)) > 1 ? ' x'.(int) $option['quantity'] : ''))->implode(', ') ?: 'None'; $removed = collect($group['removed'] ?? [])->pluck('name')->implode(', '); return ($group['group_name'] ?? 'Options').': '.$selected.($removed ? ' (Removed: '.$removed.')' : ''); })->implode('; ') }}</div>
                            @elseif ($legacyAddons->isNotEmpty())<br><strong>Extras:</strong> {{ $legacyAddons->map(fn ($addon) => ($addon['name'] ?? 'Addon').(((int) ($addon['quantity'] ?? 1)) > 1 ? ' x'.(int) $addon['quantity'] : ''))->implode(', ') }}</div>@endif
                        </td><td>{{ $item->quantity }}</td><td>{{ Money::format($item->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="totals"><table><tr><td>Subtotal</td><td>{{ Money::format($order->subtotal) }}</td></tr><tr><td>Delivery fee</td><td>{{ Money::format($order->delivery_fee) }}</td></tr><tr><td>Total</td><td>{{ Money::format($order->total) }}</td></tr></table></div>
        </div>
        <footer class="foot">Thank you for ordering from {{ $restaurant?->name ?? 'us' }}. Please keep this invoice for your records.</footer>
    </article>
</main>
<script>
function downloadInvoice(format) {
    const sheet = document.getElementById('invoice-sheet');
    const clone = sheet.cloneNode(true);
    clone.querySelectorAll('img').forEach((image) => image.remove());
    const width = sheet.offsetWidth;
    const height = sheet.offsetHeight;
    const source = new XMLSerializer().serializeToString(clone);
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}"><foreignObject width="100%" height="100%"><div xmlns="http://www.w3.org/1999/xhtml" style="font-family:Arial,sans-serif">${source}</div></foreignObject></svg>`;
    const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const image = new Image();
    image.onload = () => { const canvas = document.createElement('canvas'); canvas.width = width * 2; canvas.height = height * 2; const context = canvas.getContext('2d'); context.scale(2, 2); context.fillStyle = '#ffffff'; context.fillRect(0, 0, width, height); context.drawImage(image, 0, 0); URL.revokeObjectURL(url); const link = document.createElement('a'); link.download = `invoice-{{ $order->order_number }}.${format}`; link.href = canvas.toDataURL(format === 'jpeg' ? 'image/jpeg' : 'image/png', .95); link.click(); };
    image.src = url;
}
</script>
</body>
</html>
