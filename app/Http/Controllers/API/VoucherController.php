<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    private $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $userId = $request->input('user_id');

        $vouchers = VoucherRepository::getVouchers(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            userId: $userId,
        );

        return ResponseFormatter::success($vouchers, 'Voucher berhasil diambil.');
    }

    public function dropdown(Request $request)
    {
        $user_id = $request->input('user_id');

        $vouchers = VoucherRepository::getUserVouchers(
            userId: $user_id,
            storeId: $this->storeId,
        );

        return ResponseFormatter::success($vouchers, 'Voucher pengguna berhasil diambil.');
    }
}
