<!doctype html>
<html lang="en">
<body style="margin:0;padding:0;background:#FFF9F5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFF9F5;padding:28px 14px;">
        <tr><td align="center"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;border-radius:24px;background:#FFFFFF;overflow:hidden;">
            <tr><td style="padding:30px;background:linear-gradient(135deg,#0F0D0C,#7A0810);color:#FFFFFF;font:800 18px Arial,sans-serif;">Arcade Kebab House</td></tr>
            <tr><td style="padding:30px;">
                <h1 style="margin:0;font:800 28px Arial,sans-serif;color:#171310;">Welcome, {{ $user->name }}.</h1>
                <p style="font:15px/1.7 Arial,sans-serif;color:#6F625B;">Your Arcade Kebab House account has been created successfully.</p>
                <p style="font:14px/1.7 Arial,sans-serif;color:#6F625B;">You can now browse the menu, save your details, place orders, and track deliveries.</p>
                <a href="{{ route('home') }}" style="display:inline-block;margin-top:12px;padding:14px 20px;border-radius:12px;background:#E60C1A;color:#FFFFFF;font:800 13px Arial,sans-serif;text-decoration:none;">Browse the menu</a>
            </td></tr>
            <tr><td style="padding:20px 30px;background:#0F0D0C;color:#D6C7BC;font:12px/1.6 Arial,sans-serif;">Thanks for joining Arcade Kebab House.</td></tr>
        </table></td></tr>
    </table>
</body>
</html>
