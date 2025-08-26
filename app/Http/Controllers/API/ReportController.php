<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\ReportRepository;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $reportType = $request->input('report_type', 'sale');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $brand = $request->input('brand');

        $reportRepository = new ReportRepository();

        if ($reportType === 'purchase') {
            $data = $reportRepository->getPurchaseReport($startDate, $endDate);
        } elseif ($reportType === 'stock') {
            $data = $reportRepository->getStockReport($brand);
        } else {
            $data = $reportRepository->getSalesReport($startDate, $endDate);
        }

        // Generate PDF
        $pdf = Pdf::loadView('reports.report', $data)
            ->setPaper('A4', 'portrait')
            ->setOption([
                'defaultFont' => 'sans-serif',
            ]);

        $pdf->render();

        return ResponseFormatter::success(
            ['pdf' => base64_encode($pdf->output())],
            'Report generated successfully'
        );
    }
}
