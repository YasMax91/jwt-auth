<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $appName }} – Reset Password Code</title>
</head>
<body style="background:#f9fafb; padding:20px; font-family:Arial,sans-serif; color:#111827;">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:32px;">
                <tr>
                    <td align="center" style="font-size:24px; font-weight:bold; padding-bottom:24px;">
                        {{ $appName }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:16px; line-height:1.5; padding-bottom:24px;">
                        {{ __('You are receiving this email because we received a password reset request for your account.') }}
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:24px 0;">
                        <div style="display:inline-block; background:#F3F4F6; border-radius:10px; padding:18px 36px; font-size:28px; font-weight:700; letter-spacing:4px; font-family: monospace; color:#111827;">
                            {{ $code }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="font-size:16px; line-height:1.5; padding-bottom:16px;">
                        {{ __('This password reset code will expire in :time.', ['time' => $expiresHuman]) }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:16px; line-height:1.5; padding-bottom:16px;">
                        {{ __('If you did not request a password reset, no further action is required.') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:16px; line-height:1.5;">
                        {{ __('Regards,') }}<br>
                        {{ $appName }}
                    </td>
                </tr>
            </table>
            <p style="font-size:12px; color:#6b7280; margin-top:16px;">
                © {{ date('Y') }} {{ $appName }}. {{ __('All rights reserved.') }}
            </p>
        </td>
    </tr>
</table>
</body>
</html>