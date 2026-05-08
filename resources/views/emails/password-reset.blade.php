<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resetare parolă iaAuto.ro</title>
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
                                Salut!
                            </h1>
                            <p style="margin:0 0 16px; color:#374151; font-size:15px; line-height:1.6;">
                                Ai cerut resetarea parolei pentru contul tău iaAuto.ro.
                            </p>
                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Apasă pe butonul de mai jos pentru a seta o parolă nouă.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px;">
                                <tr>
                                    <td bgcolor="#C81424" style="border-radius:8px;">
                                        <a href="{{ $url }}" style="display:inline-block; padding:13px 22px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700;">
                                            Resetează parola
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px; color:#374151; font-size:15px; line-height:1.6;">
                                Linkul de resetare este valabil 60 de minute. Dacă nu ai cerut resetarea parolei,
                                poți ignora acest email.
                            </p>
                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Mulțumim,<br>
                                iaAuto.ro
                            </p>

                            <div style="padding-top:18px; border-top:1px solid #e5e7eb;">
                                <p style="margin:0 0 8px; color:#6b7280; font-size:13px; line-height:1.5;">
                                    Dacă butonul „Resetează parola” nu funcționează, copiază și deschide linkul de mai jos în browser:
                                </p>
                                <p style="margin:0; color:#6b7280; font-size:13px; line-height:1.5; word-break:break-all;">
                                    <a href="{{ $url }}" style="color:#C81424;">{{ $url }}</a>
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
