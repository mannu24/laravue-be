<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2d6a4f 0%,#41B883 100%);padding:32px 40px;text-align:center;">
                            <img src="{{ asset('assets/front/logo/logo.png') }}" alt="{{ config('app.name', 'Laravue') }}" style="height:40px;width:auto;" />
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 40px 16px;text-align:center;">
                            <p style="margin:0 0 8px;font-size:14px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Verification Code</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#4b5563;">Enter this code to verify your email address.</p>

                            <!-- OTP Code -->
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
                                <tr>
                                    <td style="background-color:#f0fdf4;border:2px solid #41B883;border-radius:12px;padding:18px 48px;">
                                        <span style="font-size:36px;font-weight:700;letter-spacing:8px;color:#166534;font-family:'Courier New',monospace;">{{ $otp }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;color:#9ca3af;">This code expires in <span style="font-weight:600;color:#4b5563;">5 minutes</span>.</p>
                        </td>
                    </tr>

                    <!-- Security Note -->
                    <tr>
                        <td style="padding:20px 40px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fefce8;border:1px solid #fde68a;border-radius:8px;">
                                <tr>
                                    <td style="padding:12px 16px;font-size:12px;color:#92400e;line-height:1.5;">
                                        If you didn't request this code, you can safely ignore this email. Someone may have entered your email by mistake.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px 28px;border-top:1px solid #e5e7eb;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} {{ config('app.name', 'Laravue') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
