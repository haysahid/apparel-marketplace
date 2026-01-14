<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Repositories\ReportRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
            $data['brands'] = Brand::orderBy('name', 'asc')->get();
        } else {
            $data = $reportRepository->getSalesReport($startDate, $endDate);
        }

        return Inertia::render('MyStore/Report', $data);
    }

    public function reportPreview(Request $request)
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

        return view('reports.report', $data);
    }
}
