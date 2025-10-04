<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;

class AdminStoreController extends Controller
{
    public function getStoreInvoices(Request $request, $storeId)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $userId = $request->input('user_id');
        $brandId = $request->input('brand_id');

        $invoices = InvoiceRepository::getInvoices(
            storeId: $storeId,
            userId: $userId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            brandId: $brandId
        );

        return ResponseFormatter::success(
            $invoices,
            'Berhasil mengambil data pesanan.'
        );
    }
}
