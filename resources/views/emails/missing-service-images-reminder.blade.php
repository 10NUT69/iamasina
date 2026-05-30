<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adaugă poze la anunțul tău</title>
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
                            <h1 style="margin:0 0 18px; color:#111827; font-size:22px; line-height:1.3;">
                                Anunțurile cu poze primesc mai multă atenție
                            </h1>
                            <p style="margin:0 0 10px; color:#374151; font-size:15px; line-height:1.6;">
                                Salut{{ !empty($user->name) ? ', ' . $user->name : '' }},
                            </p>
                            <p style="margin:0 0 18px; color:#374151; font-size:15px; line-height:1.6;">
                                Am observat că pe {{ $periodLabel }} ai publicat
                                {{ $serviceCount === 1 ? 'un anunț fără poze' : $serviceCount . ' anunțuri fără poze' }}
                                pe iaAuto.ro.
                            </p>
                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Pozele îi ajută pe cumpărători să vadă mai repede starea mașinii și cresc șansele să fii contactat.
                            </p>

                            @if(!empty($listingItems))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                                    @foreach($listingItems as $item)
                                        <tr>
                                            <td style="padding:13px 14px; border-bottom:{{ $loop->last ? '0' : '1px solid #e5e7eb' }}; color:#111827; font-size:14px; line-height:1.5;">
                                                <strong style="font-weight:700;">{{ $item['label'] }}</strong>
                                                @if(!empty($item['publishedAt']))
                                                    <span style="display:block; margin-top:2px; color:#6b7280; font-size:12px;">
                                                        Publicat: {{ $item['publishedAt'] }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px;">
                                <tr>
                                    <td bgcolor="#C81424" style="border-radius:8px;">
                                        <a href="{{ $accountUrl }}" style="display:inline-block; padding:13px 22px; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700;">
                                            Adaugă poze în cont
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; color:#374151; font-size:15px; line-height:1.6;">
                                Recomandarea noastră este să adaugi poze clare din exterior, interior și bord. Un anunț complet inspiră mai multă încredere.
                            </p>

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
