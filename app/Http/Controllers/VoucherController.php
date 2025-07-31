<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\Voucher;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $storeId = request()->get('store_id');
        $limit = request()->get('limit', 10);
        $search = request()->get('search');
        $orderBy = request()->get('order_by', 'created_at');
        $orderDirection = request()->get('order_direction', 'desc');

        $vouchers = VoucherRepository::getVouchers(
            storeId: $storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            activeOnly: true,
        );

        return ResponseFormatter::success(
            $vouchers,
            'Daftar voucher berhasil diambil.'
        );
    }
}
