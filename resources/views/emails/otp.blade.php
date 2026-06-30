<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>

<body style="margin:0;padding:0;background:#eef3f8;color:#142033;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3f8;margin:0;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #dbe3ee;border-radius:14px;overflow:hidden;box-shadow:0 18px 44px rgba(20,32,51,.12);">
                    <tr>
                        <td style="padding:24px 28px;background:#f8fbff;border-bottom:1px solid #dbe3ee;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="64" style="vertical-align:middle;">
                                        <img src="{{ $appLogo }}" width="54" height="54" alt="{{ $appName }}" style="display:block;width:54px;height:54px;border-radius:12px;object-fit:contain;background:#ffffff;border:1px solid #e6edf5;">
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <div style="font-size:20px;font-weight:800;line-height:1.2;color:#10213a;">{{ $appName }}</div>
                                        <div style="margin-top:4px;font-size:13px;line-height:1.4;color:#66758a;">{{ $institutionName }} {{ $appSub }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 28px 10px;">
                            <div style="font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#2563eb;">Verification code</div>
                            <h1 style="margin:10px 0 12px;font-size:28px;line-height:1.2;color:#142033;">{{ $title }}</h1>
                            <p style="margin:0;font-size:15px;line-height:1.7;color:#4d5f75;">{{ $intro }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 28px;">
                            <div style="border:1px solid #c9d8eb;background:#f6f9fd;border-radius:12px;padding:24px;text-align:center;">
                                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#66758a;">Your code</div>
                                <div style="margin-top:10px;font-size:38px;line-height:1;font-weight:800;letter-spacing:8px;color:#1e3a8a;">{{ $code }}</div>
                                <div style="margin-top:14px;font-size:13px;color:#66758a;">Expires in 10 minutes.</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 30px;">
                            <p style="margin:0;font-size:14px;line-height:1.7;color:#4d5f75;">Enter this code on the {{ $appName }} page that requested it. For your security, do not share this code with anyone.</p>
                            <p style="margin:18px 0 0;font-size:13px;line-height:1.6;color:#66758a;">If you did not request this email, you can safely ignore it.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px;background:#10213a;color:#d9e6f5;">
                            <div style="font-size:13px;font-weight:700;">{{ $appName }}</div>
                            <div style="margin-top:4px;font-size:12px;line-height:1.5;color:#a9bad0;">Secure attendance and account access for {{ $institutionName }}.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
