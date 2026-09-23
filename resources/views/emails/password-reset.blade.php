<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="margin:0;padding:0;background:#F8FAFC;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1E293B;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#F8FAFC;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #E2E8F0;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#0B1F3A;padding:28px 28px 24px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#F16029;font-weight:700;">KoraLink Academy</p>
                            <h1 style="margin:10px 0 0;font-size:24px;line-height:1.3;color:#ffffff;font-weight:700;">Reset Your Password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#334155;">
                                Someone requested a password reset for your KoraLink Academy account. If this was you, click the button below to create a new password.
                            </p>
                            <p style="margin:0 0 28px;text-align:center;">
                                <a href="{{ $url }}" style="display:inline-block;padding:14px 28px;background:#F16029;color:#ffffff;text-decoration:none;border-radius:12px;font-weight:700;font-size:15px;">
                                    Reset Password
                                </a>
                            </p>
                            <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#64748B;">
                                This link will expire in {{ $expire }} minutes.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748B;">
                                If you did not request a password reset, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px;border-top:1px solid #E2E8F0;background:#F8FAFC;">
                            <p style="margin:0;font-size:12px;color:#94A3B8;">
                                &copy; {{ date('Y') }} {{ config('app.name') }} · Digital Jobs for Youth in Health
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
