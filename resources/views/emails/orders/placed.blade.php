@php
    $badge = 'Order received';
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
                                Thanks, {{ $order->customer_name }}. We received your order.
                            </h1>
                            <p style="margin:12px 0 0;font-family:Arial,sans-serif;font-size:15px;line-height:1.7;color:#6F625B;">
                                Your fresh meal is now in the queue. We’ll email you again as soon as the restaurant confirms it.
                            </p>
                            <div style="margin:20px 0;padding:14px 16px;border-radius:16px;background:#FFF1D6;color:#7A0810;font-family:Arial,sans-serif;font-size:13px;font-weight:800;">
                                Order number: {{ $order->order_number }}
                            </div>
                            @include('emails.partials.order-bill')
                            @include('emails.partials.order-details')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 30px;background:#0F0D0C;font-family:Arial,sans-serif;font-size:12px;line-height:1.6;color:#D6C7BC;">
                            You received this email because an order was placed with Arcade Kebab House.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
