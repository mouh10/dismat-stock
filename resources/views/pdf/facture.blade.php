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
        .badge-statut {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #eaf6ff;
            color: #0a6699;
            margin-top: 6px;
        }
        .divider { border-top: 2px solid #01225a; margin: 14px 0; }
        .client-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            width: 45%;
        }
        .client-box .label { font-size: 9px; text-transform: uppercase; color: #94a3b8; font-weight: bold; margin-bottom: 4px; }
        .client-box .name { font-size: 13px; font-weight: bold; color: #01225a; margin-bottom: 2px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
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
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        table.items tbody td.num { text-align: right; }
        table.items tbody tr:nth-child(even) { background-color: #f8fafc; }

        table.totals { width: 45%; margin-left: 55%; border-collapse: collapse; }
        table.totals td { padding: 5px 10px; font-size: 10.5px; }
        table.totals td.label { color: #64748b; }
        table.totals td.value { text-align: right; font-weight: bold; color: #01225a; }
        table.totals tr.grand-total td {
            border-top: 2px solid #01225a;
            font-size: 13px;
            padding-top: 8px;
        }
        table.totals tr.grand-total td.value { color: #0a6699; }

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

    @php
        $typeLabels = ['devis' => 'Devis', 'avoir' => 'Avoir'];
        $docLabel = $typeLabels[$facture->type_doc] ?? 'Facture';
    @endphp

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
                    @if ($tenant->email) {{ $tenant->email }}<br>@endif
                    @if ($tenant->ninea) NINEA: {{ $tenant->ninea }}@endif
                    @if ($tenant->rccm) &nbsp;·&nbsp; RCCM: {{ $tenant->rccm }}@endif
                </div>
            </td>
            <td style="width: 45%;">
                <div class="doc-title">{{ $docLabel }}</div>
                <div class="doc-meta">
                    N° {{ $facture->num_facture }}<br>
                    Date : {{ $facture->date_facture->format('d/m/Y') }}<br>
                    @if ($facture->date_echeance) Échéance : {{ $facture->date_echeance->format('d/m/Y') }}<br> @endif
                    Vendeur : {{ $facture->utilisateur?->nom }}
                </div>
                <div style="text-align: right;">
                    <span class="badge-statut">{{ str_replace('_', ' ', $facture->statut) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="client-box">
        <div class="label">Facturé à</div>
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

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="num">Qté</th>
                <th class="num">Prix unit.</th>
                <th class="num">Remise</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facture->items as $item)
                <tr>
                    <td>{{ $item->designation }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($item->qte, 2, ',', ' '), '0'), ',') }}</td>
                    <td class="num">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} F</td>
                    <td class="num">{{ $item->remise > 0 ? number_format($item->remise, 0, ',', ' ') . ' F' : '—' }}</td>
                    <td class="num">{{ number_format($item->total_ht, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Montant HT</td>
            <td class="value">{{ number_format($facture->montant_ht, 0, ',', ' ') }} F</td>
        </tr>
        @if ($facture->montant_remise > 0)
        <tr>
            <td class="label">Remise</td>
            <td class="value">- {{ number_format($facture->montant_remise, 0, ',', ' ') }} F</td>
        </tr>
        @endif
        @if ($facture->tva > 0)
        <tr>
            <td class="label">TVA ({{ rtrim(rtrim(number_format($facture->taux_tva, 2), '0'), '.') }}%)</td>
            <td class="value">{{ number_format($facture->tva, 0, ',', ' ') }} F</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td class="label">Total TTC</td>
            <td class="value">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} F</td>
        </tr>
        <tr>
            <td class="label">Payé</td>
            <td class="value">{{ number_format($facture->montant_paye, 0, ',', ' ') }} F</td>
        </tr>
        @if ($facture->resteAPayer() > 0)
        <tr>
            <td class="label">Reste à payer</td>
            <td class="value">{{ number_format($facture->resteAPayer(), 0, ',', ' ') }} F</td>
        </tr>
        @endif
    </table>

    @if ($facture->notes)
        <div style="margin-top: 20px; font-size: 10px; color: #64748b;">
            <strong>Notes :</strong> {{ $facture->notes }}
        </div>
    @endif

    <div class="footer">
        {{ $tenant->mentions_legales ?? 'Merci de votre confiance.' }}<br>
        {{ $tenant->nom }} @if($tenant->ninea) — NINEA {{ $tenant->ninea }} @endif
    </div>

</body>
</html>
