@php
    use App\Support\Money;
    $restaurant = $menuItem->restaurant ?? \App\Models\Restaurant::current();
@endphp
<!doctype html>
<html lang="en">
<body style="margin:0;padding:0;background:#FFF9F5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFF9F5;padding:28px 14px;">
        <tr><td align="center"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;border-radius:24px;background:#FFFFFF;overflow:hidden;">
            <tr><td style="padding:30px;background:linear-gradient(135deg,#0F0D0C,#7A0810);color:#FFFFFF;font:800 18px Arial,sans-serif;">{{ $restaurant?->name ?: config('app.name') }}</td></tr>
            <tr><td style="padding:30px;">
                <div style="font:800 11px Arial,sans-serif;letter-spacing:2px;text-transform:uppercase;color:#E60C1A;">Menu update</div>
                <h1 style="margin:10px 0 0;font:800 28px/1.18 Arial,sans-serif;color:#171310;">{{ $menuItem->name }}</h1>
                <p style="margin:16px 0;font:15px/1.7 Arial,sans-serif;color:#6F625B;">The price for this menu item has been updated.</p>
                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:20px 0;background:#FFF9F5;border:1px solid #E8DED6;border-radius:16px;">
                    <tr><td style="padding:16px;font:13px Arial,sans-serif;color:#6F625B;">Previous price</td><td align="right" style="padding:16px;font:800 15px Arial,sans-serif;color:#6F625B;">{{ Money::format($previousPrice) }}</td></tr>
                    <tr><td style="padding:16px;border-top:1px solid #E8DED6;font:13px Arial,sans-serif;color:#6F625B;">Current price</td><td align="right" style="padding:16px;border-top:1px solid #E8DED6;font:900 18px Arial,sans-serif;color:#E60C1A;">{{ Money::format($menuItem->price) }}</td></tr>
                </table>
                <a href="{{ $menuItem->public_url }}" style="display:inline-block;padding:14px 20px;border-radius:12px;background:#E60C1A;color:#FFFFFF;font:800 13px Arial,sans-serif;text-decoration:none;">View menu item</a>
            </td></tr>
            <tr><td style="padding:20px 30px;background:#0F0D0C;color:#D6C7BC;font:12px/1.6 Arial,sans-serif;">You received this because you have an Arcade Kebab House customer account.</td></tr>
        </table></td></tr>
    </table>
</body>
</html>
