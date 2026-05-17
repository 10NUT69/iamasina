<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmare publicare anunț iaAuto.ro</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; color:#1f2937; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6; margin:0; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:580px; background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:24px 28px; background:#111827;">
                            <a href="{{ config('app.url') }}" style="color:#ffffff; text-decoration:none; font-size:20px; font-weight:700;">
                                iaAuto.ro
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 28px;">
                            <h1 style="margin:0 0 16px; color:#111827; font-size:22px; line-height:1.3;">
                                Salut{{ !empty($user->name) ? ', ' . $user->name : '' }}!
                            </h1>
                            <p style="margin:0 0 16px; color:#374151; font-size:15px; line-height:1.6;">
                                Îți confirmăm că anunțul tău a fost primit pe iaAuto.ro.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:10px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 6px; color:#6b7280; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;">
                                            Anunț publicat
                                        </p>
                                        <p style="margin:0; color:#111827; font-size:17px; line-height:1.4; font-weight:700;">
                                            {{ $service->title }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Îl poți verifica și administra oricând din contul tău.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px;">
                                <tr>
                                    <td bgcolor="#C81424" style="border-radius:8px;">
                                        <a href="{{ $accountUrl }}" style="display:inline-block; padding:13px 22px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700;">
                                            Vezi anunțurile mele
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Mulțumim,<br>
                                iaAuto.ro
                            </p>

                            <div style="padding-top:18px; border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 8px; color:#6b7280; font-size:13px; line-height:1.5;">
                                    Dacă butonul nu funcționează, copiază și deschide linkul de mai jos în browser:
                                </p>
                                <p style="margin:0; color:#6b7280; font-size:13px; line-height:1.5; word-break:break-all;">
                                    <a href="{{ $accountUrl }}" style="color:#C81424;">{{ $accountUrl }}</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 28px; background:#f9fafb; color:#6b7280; font-size:12px;">
                            © {{ date('Y') }} iaAuto.ro. Toate drepturile rezervate.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
