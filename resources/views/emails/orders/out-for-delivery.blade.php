@php($badge = 'Out for delivery')
<!doctype html>
<html lang="en">
<body style="margin:0;padding:0;background:#FFF9F5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFF9F5;padding:28px 14px;">
        <tr><td align="center"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;border-radius:24px;background:#FFFFFF;overflow:hidden;">
            <tr><td>@include('emails.partials.header')</td></tr>
            <tr><td style="padding:30px;">
                <h1 style="margin:0;font:800 28px/1.18 Arial,sans-serif;color:#171310;">Your order is on the way.</h1>
                <p style="margin:12px 0 0;font:15px/1.7 Arial,sans-serif;color:#6F625B;">Order {{ $order->order_number }} has left the restaurant and is out for delivery.</p>
                @include('emails.partials.order-bill')
                @include('emails.partials.order-details')
            </td></tr>
            <tr><td style="padding:20px 30px;background:#0F0D0C;color:#D6C7BC;font:12px/1.6 Arial,sans-serif;">Please keep your phone available for the rider.</td></tr>
        </table></td></tr>
    </table>
</body>
</html>
