@php
    $paymentLabel = strtoupper((string) $order->payment_method) === 'COD' ? 'Cash on Delivery' : ucfirst((string) $order->payment_method);
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;border:1px solid #E8DED6;border-radius:18px;background:#FFFFFF;">
    <tr>
        <td style="padding:18px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding-bottom:13px;font-family:Arial,sans-serif;font-size:15px;font-weight:800;color:#171310;">
                        Delivery details
                    </td>
                </tr>
                <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;line-height:1.65;color:#6F625B;">
                        <strong style="color:#171310;">{{ $order->customer_name }}</strong><br>
                        {{ $order->customer_phone }}<br>
                        {{ $order->delivery_address }}<br>
                        Payment: {{ $paymentLabel }} · {{ ucfirst((string) $order->payment_status) }}
                    </td>
                </tr>
                @if ($order->rider)
                    <tr>
                        <td style="padding-top:13px;font-family:Arial,sans-serif;font-size:13px;line-height:1.65;color:#6F625B;">
                            Rider: <strong style="color:#171310;">{{ $order->rider->name }}</strong>
                            @if ($order->rider->phone)
                                · {{ $order->rider->phone }}
                            @endif
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
