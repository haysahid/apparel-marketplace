<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Repositories\BrandRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyStoreOrderController extends Controller
{
    protected $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $brandId = $request->input('brand_id');

        $invoices = InvoiceRepository::getInvoices(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            brandId: $brandId
        );

        $brands = BrandRepository::getBrandDropdown(
            storeId: $this->storeId,
        );

        return Inertia::render('MyStore/Order', [
            'invoices' => $invoices,
            'brands' => $brands,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoiceDetail = InvoiceRepository::getInvoiceDetail(
            invoiceId: $invoice->id,
        );

        return Inertia::render('MyStore/Order/EditInvoice', $invoiceDetail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
