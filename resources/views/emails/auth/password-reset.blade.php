<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your IRDCRP MIS password</title>
</head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:36px 16px;background:#f1f5f9;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">
                <tr><td style="padding:26px 32px;background:#064e3b;color:#ffffff;">
                    <p style="margin:0;font-size:12px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#a7f3d0;">IRDCRP MIS</p>
                    <h1 style="margin:10px 0 0;font-size:23px;line-height:1.3;">Reset your password</h1>
                </td></tr>
                <tr><td style="padding:32px;">
                    <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">Hello {{ $userName }},</p>
                    <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#475569;">We received a request to reset the password for your IRDCRP MIS account. Use the button below to choose a new password.</p>
                    <p style="margin:0 0 26px;"><a href="{{ $resetUrl }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#047857;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;">Reset password</a></p>
                    <p style="margin:0 0 12px;font-size:13px;line-height:1.7;color:#64748b;">This link expires in {{ $expiresIn }} minutes and can only be used once.</p>
                    <p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#64748b;">If the button does not work, copy and paste this link into your browser:</p>
                    <p style="margin:0;word-break:break-all;font-size:12px;line-height:1.7;"><a href="{{ $resetUrl }}" style="color:#047857;">{{ $resetUrl }}</a></p>
                    <hr style="margin:26px 0;border:0;border-top:1px solid #e2e8f0;">
                    <p style="margin:0;font-size:13px;line-height:1.7;color:#64748b;">If you did not request a password reset, you can ignore this email. Your password will remain unchanged.</p>
                </td></tr>
                <tr><td style="padding:18px 32px;background:#f8fafc;color:#94a3b8;font-size:12px;">IRDCRP Management Information System · Automated security message</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
