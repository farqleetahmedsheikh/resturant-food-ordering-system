<!doctype html>
<html lang="en">
<body style="margin:0;padding:0;background:#FFF9F5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFF9F5;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;border-radius:24px;background:#FFFFFF;box-shadow:0 1px 2px rgba(74,9,13,.04),0 10px 28px rgba(74,9,13,.07);overflow:hidden;">
                    <tr>
                        <td style="padding:30px;background:radial-gradient(circle at 86% 10%, rgba(230,12,26,.36), transparent 34%), linear-gradient(135deg,#0F0D0C 0%,#281416 52%,#7A0810 100%);">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:48px;height:48px;border-radius:16px;background:#E60C1A;color:#FFFFFF;font-family:Arial,sans-serif;font-size:16px;font-weight:800;text-align:center;vertical-align:middle;">
                                        AK
                                    </td>
                                    <td style="padding-left:14px;">
                                        <div style="font-family:Arial,sans-serif;font-size:18px;font-weight:800;line-height:1.2;color:#FFFFFF;">
                                            Arcade Kebab House
                                        </div>
                                        <div style="margin-top:4px;font-family:Arial,sans-serif;font-size:11px;font-weight:800;letter-spacing:2.4px;text-transform:uppercase;color:#FAB005;">
                                            Secure account reset
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            <h1 style="margin:0;font-family:Arial,sans-serif;font-size:27px;line-height:1.18;color:#171310;">
                                Your password reset code
                            </h1>
                            <p style="margin:12px 0 0;font-family:Arial,sans-serif;font-size:15px;line-height:1.7;color:#6F625B;">
                                Hi {{ $user->name }}, use this one-time code to reset your Arcade Kebab House password.
                            </p>
                            <div style="margin:24px 0;padding:22px;border-radius:20px;background:#FFF1D6;border:1px solid #F4B7BC;text-align:center;">
                                <div style="font-family:Arial,sans-serif;font-size:34px;font-weight:900;letter-spacing:9px;color:#7A0810;">
                                    {{ $otp }}
                                </div>
                                <div style="margin-top:10px;font-family:Arial,sans-serif;font-size:12px;font-weight:800;letter-spacing:1.6px;text-transform:uppercase;color:#B77900;">
                                    Expires in {{ $expiresInMinutes }} minutes
                                </div>
                            </div>
                            <p style="margin:0;font-family:Arial,sans-serif;font-size:13px;line-height:1.7;color:#6F625B;">
                                If you did not request this code, you can safely ignore this email. Never share this code with anyone.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 30px;background:#0F0D0C;font-family:Arial,sans-serif;font-size:12px;line-height:1.6;color:#D6C7BC;">
                            This security email was sent by Arcade Kebab House.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
