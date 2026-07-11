<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your login code</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f5;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="420" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e4e4e7;">
                    <tr>
                        <td style="background:#16233F;padding:20px 28px;">
                            <span style="color:#ffffff;font-size:18px;font-weight:bold;">Shelvi Finance</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 8px;font-size:15px;color:#18181b;">Hi {{ $user->name }},</p>
                            <p style="margin:0 0 20px;font-size:14px;color:#52525b;line-height:1.5;">
                                Use this code to finish signing in to your Shelvi Finance account.
                                It expires in {{ config('otp.ttl_minutes') }} minutes.
                            </p>
                            <div style="text-align:center;margin:0 0 20px;">
                                <span style="display:inline-block;padding:14px 28px;border-radius:8px;background:#f4f4f5;
                                    font-size:32px;font-weight:bold;letter-spacing:8px;color:#16233F;">{{ $code }}</span>
                            </div>
                            <p style="margin:0;font-size:12px;color:#a1a1aa;line-height:1.5;">
                                If you didn't try to sign in, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
