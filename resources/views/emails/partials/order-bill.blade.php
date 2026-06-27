@php
    use App\Support\Money;
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #E8DED6;border-radius:18px;overflow:hidden;background:#FFFFFF;">
    <tr>
        <td style="padding:18px 20px;border-bottom:1px solid #E8DED6;background:#FFF9F5;">
            <div style="font-family:Arial,sans-serif;font-size:15px;font-weight:800;color:#171310;">
                Order items
            </div>
        </td>
    </tr>

    @foreach ($order->items as $item)
        @php
            $addons = collect($item->addons_snapshot ?? []);
        @endphp
        <tr>
            <td style="padding:16px 20px;border-bottom:1px solid #E8DED6;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="vertical-align:top;padding-right:14px;">
                            <div style="font-family:Arial,sans-serif;font-size:14px;font-weight:800;line-height:1.35;color:#171310;">
                                {{ $item->item_name }}
                            </div>

                            <div style="margin-top:6px;font-family:Arial,sans-serif;font-size:12px;line-height:1.55;color:#6F625B;">
                                Qty {{ $item->quantity }}
                                @if ($item->size_name)
                                    &nbsp;•&nbsp; {{ $item->size_name }}
                                @endif
                                &nbsp;•&nbsp; {{ Money::format($item->price) }} each
                            </div>

                            @if ($addons->isNotEmpty())
                                <div style="margin-top:8px;font-family:Arial,sans-serif;font-size:12px;line-height:1.55;color:#6F625B;">
                                    Extras:
                                    {{ $addons->map(fn ($addon) => ($addon['name'] ?? 'Addon').' (+'.Money::format($addon['price'] ?? 0).')')->implode(', ') }}
                                </div>
                            @endif
                        </td>
                        <td align="right" style="vertical-align:top;white-space:nowrap;font-family:Arial,sans-serif;font-size:14px;font-weight:800;color:#B77900;">
                            {{ Money::format($item->total) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach

    <tr>
        <td style="padding:18px 20px;background:#FFF9F5;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:5px 0;font-family:Arial,sans-serif;font-size:13px;color:#6F625B;">Subtotal</td>
                    <td align="right" style="padding:5px 0;font-family:Arial,sans-serif;font-size:13px;font-weight:700;color:#171310;">{{ Money::format($order->subtotal) }}</td>
                </tr>
                <tr>
                    <td style="padding:5px 0;font-family:Arial,sans-serif;font-size:13px;color:#6F625B;">Delivery fee</td>
                    <td align="right" style="padding:5px 0;font-family:Arial,sans-serif;font-size:13px;font-weight:700;color:#171310;">{{ Money::format($order->delivery_fee) }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 0 0;border-top:1px solid #E8DED6;font-family:Arial,sans-serif;font-size:16px;font-weight:900;color:#171310;">Total</td>
                    <td align="right" style="padding:12px 0 0;border-top:1px solid #E8DED6;font-family:Arial,sans-serif;font-size:18px;font-weight:900;color:#E60C1A;">{{ Money::format($order->total) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
