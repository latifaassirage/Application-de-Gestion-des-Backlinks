<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Source Summaries</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding: 20px; background-color: #f9f9f9; }
        .header h1 { margin: 0; color: #2c3e50; font-size: 22px; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        
        .stats-container { width: 100%; margin-bottom: 20px; text-align: center; }
        .stat-box { display: inline-block; width: 16%; padding: 10px; background: #fff; border: 1px solid #eee; margin: 0 5px; }
        .stat-box h4 { margin: 0; color: #7f8c8d; font-size: 9px; text-transform: uppercase; }
        .stat-box p { margin: 5px 0 0; font-size: 14px; font-weight: bold; color: #2c3e50; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; word-wrap: break-word; }
        th { background-color: #2c3e50; color: white; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        
        .cost-free { color: #27ae60; font-weight: bold; }
        .link-type-dofollow { color: #2980b9; font-weight: bold; }
        .link-type-nofollow { color: #e67e22; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT DES SOURCE SUMMARIES</h1>
        <p>Généré le : {{ date('d/m/Y H:i') }}</p>
        <p><strong>Période :</strong> Rapport Global - Toutes les données</p>
    </div>

    <div class="stats-container">
        <div class="stat-box"><h4>Total</h4><p>{{ $stats['total'] }}</p></div>
        <div class="stat-box"><h4>Gratuit</h4><p>{{ $stats['free'] }}</p></div>
        <div class="stat-box"><h4>Payant</h4><p>{{ $stats['paid'] }}</p></div>
        <div class="stat-box"><h4>DoFollow</h4><p>{{ $stats['dofollow'] }}</p></div>
        <div class="stat-box"><h4>NoFollow</h4><p>{{ $stats['nofollow'] }}</p></div>
        <div class="stat-box"><h4>Coût Total</h4><p>${{ number_format($stats['total_cost'], 2) }}</p></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Website</th>
                <th style="width: 10%;">Coût</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 25%;">Email Contact</th>
                <th style="width: 10%;">Spam Score</th>
             </tr>
        </thead>
        <tbody>
            DEBUG: {{ $summaries->count() }} éléments trouvés
            @foreach($summaries as $summary)
            <tr>
                <td><strong>{{ $summary->website }}</strong></td>
                <td>
                    @if($summary->cost == 0 || $summary->cost === "0" || $summary->cost === null)
                        <span class="cost-free">Free</span>
                    @else
                        ${{ number_format($summary->cost, 2) }}
                    @endif
                </td>
                <td>
                    @if($summary->link_type === 'DoFollow')
                        <span class="link-type-dofollow">{{ $summary->link_type }}</span>
                    @elseif($summary->link_type === 'NoFollow')
                        <span class="link-type-nofollow">{{ $summary->link_type }}</span>
                    @else
                        {{ $summary->link_type ?? '-' }}
                    @endif
                </td>
                <td>{{ $summary->contact_email ?? '-' }}</td>
                <td>{{ $summary->spam ?? 0 }}%</td>
               
             @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gestion Backlinks - Rapport Source Summaries - Page {PAGENO}
    </div>
</body>
</html>
