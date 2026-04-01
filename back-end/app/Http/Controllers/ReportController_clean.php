<?php

namespace App\Http\Controllers;

use App\Models\Backlink;
use App\Models\Client;
use App\Models\SourceSummary;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generatePdf(Request $request, $clientId = null)
    {
        try {
            $query = Backlink::with(['sourceSite', 'client']);
            
            if ($clientId && $clientId !== 'null') {
                $query->where('client_id', $clientId);
            }
            
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $selectedColumns = $request->columns ?? [];

            if ($startDate) {
                $query->whereDate('date_added', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('date_added', '<=', $endDate);
            }
            
            $backlinks = $query->get();
            
            $stats = [
                'total' => $backlinks->count(),
                'live' => $backlinks->where('status', 'Live')->count(),
                'lost' => $backlinks->where('status', 'Lost')->count(),
                'pending' => $backlinks->where('status', 'Pending')->count(),
                'free' => $backlinks->where('cost', 0)->count(),
                'paid' => $backlinks->where('cost', '>', 0)->count(),
                'total_cost' => (float) $backlinks->sum('cost')
            ];

            $pdf = Pdf::loadView('reports.pdf', compact('backlinks', 'stats', 'startDate', 'endDate', 'selectedColumns'));
            return $pdf->download('rapport.pdf');

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function generateExcel(Request $request, $clientId = null)
    {
        try {
            $query = Backlink::with(['sourceSite', 'client']);
            if ($clientId && $clientId !== 'null') {
                $query->where('client_id', $clientId);
            }
            
            if ($request->start_date) {
                $query->whereDate('date_added', '>=', $request->start_date);
            }
            if ($request->end_date) {
                $query->whereDate('date_added', '<=', $request->end_date);
            }

            $backlinks = $query->get();
            $selectedColumns = $request->columns ?? [];

            $fileName = 'backlinks_report.csv';
            $headers = [
                "Content-type" => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$fileName",
            ];

            $callback = function() use($backlinks, $selectedColumns) {
                $file = fopen('php://output', 'w');
                // حل مشكل Excel: نزيدو الـ BOM و سطر التعريف بالسيباراتور
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 
                fputs($file, "sep=,\n"); // هادي هي اللي غاتنظم الخانات فـ Excel أوتوماتيكياً

                // Dynamic headers based on selected columns
                $headers = [];
                if ($selectedColumns['date_added'] ?? true) $headers[] = 'Date Added';
                if ($selectedColumns['source_website'] ?? true) $headers[] = 'Source Website';
                if ($selectedColumns['traffic'] ?? true) $headers[] = 'Traffic';
                if ($selectedColumns['type'] ?? true) $headers[] = 'Type';
                if ($selectedColumns['target_url'] ?? true) $headers[] = 'Target URL';
                if ($selectedColumns['anchor_text'] ?? true) $headers[] = 'Anchor Text';
                if ($selectedColumns['placement_url'] ?? true) $headers[] = 'Placement URL';
                if ($selectedColumns['status'] ?? true) $headers[] = 'Status';
                if ($selectedColumns['quality_score'] ?? true) $headers[] = 'Quality Score';
                if ($selectedColumns['cost'] ?? true) $headers[] = 'Cost';
                
                fputcsv($file, $headers);

                foreach ($backlinks as $link) {
                    $row = [];
                    if ($selectedColumns['date_added'] ?? true) $row[] = $link->date_added;
                    if ($selectedColumns['source_website'] ?? true) $row[] = $link->sourceSite->domain ?? 'N/A';
                    if ($selectedColumns['traffic'] ?? true) $row[] = $link->sourceSite->traffic_estimated ?? 'N/A';
                    if ($selectedColumns['type'] ?? true) $row[] = $link->type;
                    if ($selectedColumns['target_url'] ?? true) $row[] = $link->target_url ?? '-';
                    if ($selectedColumns['anchor_text'] ?? true) $row[] = $link->anchor_text ?? '-';
                    if ($selectedColumns['placement_url'] ?? true) $row[] = $link->placement_url ?? '-';
                    if ($selectedColumns['status'] ?? true) $row[] = $link->status;
                    if ($selectedColumns['quality_score'] ?? true) $row[] = $link->sourceSite->quality_score ?? 'N/A';
                    if ($selectedColumns['cost'] ?? true) $row[] = $link->cost;
                    
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
