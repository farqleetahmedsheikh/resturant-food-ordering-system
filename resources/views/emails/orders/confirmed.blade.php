@php
    $badge = 'Confirmed';
@endphp

<!doctype html>
<html lang="en">
<body style="margin:0;padding:0;background:#FFF9F5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFF9F5;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;border-radius:24px;background:#FFFFFF;box-shadow:0 1px 2px rgba(74,9,13,.04),0 10px 28px rgba(74,9,13,.07);overflow:hidden;">
                    <tr><td>@include('emails.partials.header')</td></tr>
                    <tr>
                        <td style="padding:30px;">
                            <h1 style="margin:0;font-family:Arial,sans-serif;font-size:28px;line-height:1.18;color:#171310;">
                                Your order is confirmed.
                            </h1>
                            <p style="margin:12px 0 0;font-family:Arial,sans-serif;font-size:15px;line-height:1.7;color:#6F625B;">
                                The kitchen has accepted order {{ $order->order_number }} and will start preparing it shortly.
                            </p>
                            <div style="margin:20px 0;padding:14px 16px;border-radius:16px;background:#F1F8E8;color:#3F7D18;font-family:Arial,sans-serif;font-size:13px;font-weight:800;">
                                Status: {{ \App\Models\Order::STATUSES[$order->order_status] ?? ucfirst((string) $order->order_status) }}
                            </div>
                            @include('emails.partials.order-bill')
                            @include('emails.partials.order-details')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 30px;background:#0F0D0C;font-family:Arial,sans-serif;font-size:12px;line-height:1.6;color:#D6C7BC;">
                            We’ll keep you posted when your order moves toward delivery.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
