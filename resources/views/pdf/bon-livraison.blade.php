<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 34px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0d2a5c;
            margin: 0;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 18px; font-weight: bold; color: #01225a; margin: 4px 0 2px; }
        .muted { color: #64748b; font-size: 10px; line-height: 1.5; }
        .doc-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #01225a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-meta { text-align: right; font-size: 10px; color: #64748b; margin-top: 4px; line-height: 1.6; }
        .divider { border-top: 2px solid #01225a; margin: 14px 0; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            width: 47%;
        }
        .info-box .label { font-size: 9px; text-transform: uppercase; color: #94a3b8; font-weight: bold; margin-bottom: 4px; }
        .info-box .name { font-size: 13px; font-weight: bold; color: #01225a; margin-bottom: 2px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items thead th {
            background-color: #01225a;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        table.items thead th.num { text-align: right; }
        table.items tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        table.items tbody td.num { text-align: right; font-weight: bold; }
        table.items tbody tr:nth-child(even) { background-color: #f8fafc; }

        .signatures { width: 100%; margin-top: 50px; border-collapse: collapse; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 10px; }
        .sign-box {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            margin-top: 50px;
        }
        .sign-label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .sign-name { font-size: 9px; color: #94a3b8; margin-top: 2px; }

        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="34" height="34">
                    <rect x="0" y="0" width="24" height="24" rx="6" fill="#0EA5EA"/>
                    <g fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m7.5 4.27 9 5.15"/>
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </g>
                </svg>
                <div class="company-name">{{ $tenant->nom }}</div>
                <div class="muted">
                    @if ($tenant->adresse) {{ $tenant->adresse }}@if($tenant->ville), {{ $tenant->ville }}@endif<br>@endif
                    @if ($tenant->telephone) Tél: {{ $tenant->telephone }}<br>@endif
                    @if ($facture->magasin) Expédié depuis : {{ $facture->magasin->nom }}@endif
                </div>
            </td>
            <td style="width: 45%;">
                <div class="doc-title">Bon de livraison</div>
                <div class="doc-meta">
                    N° BL-{{ $facture->num_facture }}<br>
                    Date : {{ $facture->date_facture->format('d/m/Y') }}<br>
                    Facture liée : {{ $facture->num_facture }}
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-box">
                    <div class="label">Livré à</div>
                    @if ($facture->client)
                        <div class="name">{{ $facture->client->nom }} {{ $facture->client->prenom }}</div>
                        <div class="muted">
                            @if ($facture->client->telephone) {{ $facture->client->telephone }}<br> @endif
                            @if ($facture->client->adresse) {{ $facture->client->adresse }} @endif
                            @if ($facture->client->ville) , {{ $facture->client->ville }} @endif
                        </div>
                    @else
                        <div class="name">Client comptoir</div>
                    @endif
                </div>
            </td>
            <td style="width: 6%;"></td>
            <td>
                <div class="info-box">
                    <div class="label">Détails</div>
                    <div class="muted">
                        Préparé par : {{ $facture->utilisateur?->nom }}<br>
                        Nombre d'articles : {{ $facture->items->sum('qte') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="num">Quantité livrée</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facture->items as $item)
                <tr>
                    <td>{{ $item->designation }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($item->qte, 2, ',', ' '), '0'), ',') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($facture->notes)
        <div style="margin-bottom: 20px; font-size: 10px; color: #64748b;">
            <strong>Notes :</strong> {{ $facture->notes }}
        </div>
    @endif

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-box">
                    <div class="sign-label">Livré par</div>
                    <div class="sign-name">Nom &amp; signature</div>
                </div>
            </td>
            <td>
                <div class="sign-box">
                    <div class="sign-label">Reçu par (client)</div>
                    <div class="sign-name">Nom &amp; signature</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Ce bon de livraison atteste de la remise des articles listés ci-dessus. Il ne constitue pas une preuve de paiement — voir la facture {{ $facture->num_facture }}.<br>
        {{ $tenant->nom }}
    </div>

</body>
</html>
