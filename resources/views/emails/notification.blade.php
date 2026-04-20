<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notification' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2d6a4f 0%,#41B883 100%);padding:32px 40px;text-align:center;">
                            <img src="{{ asset('assets/front/logo/logo.png') }}" alt="{{ config('app.name', 'Laravue') }}" style="height:40px;width:auto;margin-bottom:10px;" />
                            <p style="margin:0;font-size:13px;color:rgba(255,255,255,0.8);">Developer Community Platform</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 40px 20px;">
                            <h2 style="margin:0 0 16px;font-size:20px;font-weight:600;color:#111827;">{{ $title ?? 'You have a new notification' }}</h2>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#4b5563;">{{ $notificationMessage ?? '' }}</p>

                            @if(isset($notifiable) && $notifiable)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <span style="font-weight:600;color:#166534;font-size:14px;">{{ $notifiable->name }}</span>
                                        <span style="color:#6b7280;font-size:13px;margin-left:6px;">&#64;{{ $notifiable->username }}</span>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            @if(isset($data['url']))
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0 8px;">
                                <tr>
                                    <td style="background-color:#41B883;border-radius:8px;">
                                        <a href="{{ url($data['url']) }}" style="display:inline-block;padding:13px 32px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">View Details &rarr;</a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 40px 32px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0 0 4px;font-size:12px;color:#9ca3af;text-align:center;">You're receiving this because you have notifications enabled.</p>
                            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">&copy; {{ date('Y') }} {{ config('app.name', 'Laravue') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
