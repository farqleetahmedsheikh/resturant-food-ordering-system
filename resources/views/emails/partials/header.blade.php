@php
    $restaurant = $order->restaurant ?? \App\Models\Restaurant::current();
    $brandName = $restaurant?->name ?: config('app.name', 'Arcade Kebab House');
    $initials = $restaurant?->initials ?: 'AK';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0F0D0C;border-radius:24px 24px 0 0;overflow:hidden;">
    <tr>
        <td style="padding:30px 30px 26px;background:radial-gradient(circle at 86% 10%, rgba(230,12,26,.36), transparent 34%), linear-gradient(135deg,#0F0D0C 0%,#281416 52%,#7A0810 100%);">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="vertical-align:middle;">
                        <table role="presentation" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="width:48px;height:48px;border-radius:16px;background:#E60C1A;color:#FFFFFF;font-family:Arial,sans-serif;font-size:16px;font-weight:800;text-align:center;vertical-align:middle;">
                                    {{ $initials }}
                                </td>
                                <td style="padding-left:14px;">
                                    <div style="font-family:Arial,sans-serif;font-size:18px;font-weight:800;line-height:1.2;color:#FFFFFF;">
                                        {{ $brandName }}
                                    </div>
                                    <div style="margin-top:4px;font-family:Arial,sans-serif;font-size:11px;font-weight:800;letter-spacing:2.4px;text-transform:uppercase;color:#FAB005;">
                                        Premium Restaurant Ordering
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    @isset($badge)
                        <td align="right" style="vertical-align:middle;">
                            <span style="display:inline-block;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.10);border-radius:999px;padding:9px 13px;font-family:Arial,sans-serif;font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:#FFFFFF;">
                                {{ $badge }}
                            </span>
                        </td>
                    @endisset
                </tr>
            </table>
        </td>
    </tr>
</table>
