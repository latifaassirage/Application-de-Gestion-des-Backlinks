<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport Backlinks</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding: 20px; background-color: #f9f9f9; }
        .header h1 { margin: 0; color: #2c3e50; font-size: 22px; }
        .header p { margin: 5px 0; color: #7f8c8d; }
        
        
        .stats-container { width: 100%; margin-bottom: 20px; text-align: center; }
        .stat-box { display: inline-block; width: 18%; padding: 10px; background: #fff; border: 1px solid #eee; margin: 0 5px; }
        .stat-box h4 { margin: 0; color: #7f8c8d; font-size: 9px; text-transform: uppercase; }
        .stat-box p { margin: 5px 0 0; font-size: 14px; font-weight: bold; color: #2c3e50; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; word-wrap: break-word; }
        th { background-color: #2c3e50; color: white; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        
        .status-live { color: #27ae60; font-weight: bold; }
        .status-lost { color: #e74c3c; font-weight: bold; }
        .url-text { font-size: 8px; color: #2980b9; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RAPPORT DE BACKLINKS</h1>
        <p>Généré le : {{ date('d/m/Y H:i') }}</p>
        @if(isset($startDate) && isset($endDate) && $startDate && $endDate)
            <p><strong>Période :</strong> Du {{ $startDate }} au {{ $endDate }}</p>
        @else
            <p><strong>Période :</strong> Rapport Global</p>
        @endif
    </div>

    <div class="stats-container">
        <div class="stat-box"><h4>Total</h4><p>{{ $stats['total'] }}</p></div>
        <div class="stat-box"><h4>Live</h4><p>{{ $stats['live'] }}</p></div>
        <div class="stat-box"><h4>Lost</h4><p>{{ $stats['lost'] }}</p></div>
        <div class="stat-box"><h4>Paid</h4><p>{{ $stats['paid'] }}</p></div>
        <div class="stat-box"><h4>Coût Total</h4><p>${{ number_format($stats['total_cost'], 2) }}</p></div>
    </div>

    <table>
        <thead>
            <tr>
                @if(isset($selectedColumns['date_added']) && $selectedColumns['date_added'])
                    <th style="width: 12%;">Date</th>
                @endif
                @if(isset($selectedColumns['source_website']) && $selectedColumns['source_website'])
                    <th style="width: 15%;">Source</th>
                @endif
                @if(isset($selectedColumns['traffic']) && $selectedColumns['traffic'])
                    <th style="width: 10%;">Traffic</th>
                @endif
                @if(isset($selectedColumns['type']) && $selectedColumns['type'])
                    <th style="width: 10%;">Type</th>
                @endif
                @if(isset($selectedColumns['target_url']) && $selectedColumns['target_url'])
                    <th style="width: 20%;">Target URL</th>
                @endif
                @if(isset($selectedColumns['anchor_text']) && $selectedColumns['anchor_text'])
                    <th style="width: 15%;">Anchor</th>
                @endif
                @if(isset($selectedColumns['placement_url']) && $selectedColumns['placement_url'])
                    <th style="width: 15%;">Placement URL</th>
                @endif
                @if(isset($selectedColumns['status']) && $selectedColumns['status'])
                    <th style="width: 10%;">Status</th>
                @endif
                @if(isset($selectedColumns['quality_score']) && $selectedColumns['quality_score'])
                    <th style="width: 10%;">Score</th>
                @endif
                @if(isset($selectedColumns['cost']) && $selectedColumns['cost'])
                    <th style="width: 10%;">Coût</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($backlinks as $link)
            <tr>
                @if(isset($selectedColumns['date_added']) && $selectedColumns['date_added'])
                    <td>{{ $link->date_added }}</td>
                @endif
                @if(isset($selectedColumns['source_website']) && $selectedColumns['source_website'])
                    <td><strong>{{ $link->sourceSite->domain ?? 'N/A' }}</strong></td>
                @endif
                @if(isset($selectedColumns['traffic']) && $selectedColumns['traffic'])
                    <td>{{ $link->sourceSite->traffic_estimated ?? 'N/A' }}</td>
                @endif
                @if(isset($selectedColumns['type']) && $selectedColumns['type'])
                    <td>{{ $link->type }}</td>
                @endif
                @if(isset($selectedColumns['target_url']) && $selectedColumns['target_url'])
                    <td class="url-text">{{ $link->target_url }}</td>
                @endif
                @if(isset($selectedColumns['anchor_text']) && $selectedColumns['anchor_text'])
                    <td>{{ $link->anchor_text ?? '-' }}</td>
                @endif
                @if(isset($selectedColumns['placement_url']) && $selectedColumns['placement_url'])
                    <td class="url-text">{{ $link->placement_url ?? '-' }}</td>
                @endif
                @if(isset($selectedColumns['status']) && $selectedColumns['status'])
                    <td class="status-{{ strtolower($link->status) }}">{{ $link->status }}</td>
                @endif
                @if(isset($selectedColumns['quality_score']) && $selectedColumns['quality_score'])
                    <td style="text-align: center;">{{ $link->sourceSite->quality_score ?? '3' }} /5</td>
                @endif
                @if(isset($selectedColumns['cost']) && $selectedColumns['cost'])
                    <td>${{ number_format($link->cost, 2) }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gestion Backlinks - Rapport Confidentiel - Page {PAGENO}
    </div>
</body>
</html>