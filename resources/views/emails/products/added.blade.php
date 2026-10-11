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
                <div style="font:800 11px Arial,sans-serif;letter-spacing:2px;text-transform:uppercase;color:#E60C1A;">New menu item</div>
                <h1 style="margin:10px 0 0;font:800 28px/1.18 Arial,sans-serif;color:#171310;">{{ $menuItem->name }}</h1>
                @if ($menuItem->category)
                    <p style="margin:8px 0 0;font:700 12px Arial,sans-serif;color:#B77900;">{{ $menuItem->category->name }}</p>
                @endif
                <p style="margin:16px 0 0;font:15px/1.7 Arial,sans-serif;color:#6F625B;">{{ $menuItem->description ?: 'A fresh new option is now available on our menu.' }}</p>
                <p style="margin:18px 0;font:900 22px Arial,sans-serif;color:#E60C1A;">{{ Money::format($menuItem->price) }}</p>
                <a href="{{ $menuItem->public_url }}" style="display:inline-block;padding:14px 20px;border-radius:12px;background:#E60C1A;color:#FFFFFF;font:800 13px Arial,sans-serif;text-decoration:none;">View item</a>
            </td></tr>
            <tr><td style="padding:20px 30px;background:#0F0D0C;color:#D6C7BC;font:12px/1.6 Arial,sans-serif;">You received this because you have an Arcade Kebab House customer account.</td></tr>
        </table></td></tr>
    </table>
</body>
</html>
