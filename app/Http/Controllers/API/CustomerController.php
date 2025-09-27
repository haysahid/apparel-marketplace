<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Repositories\UserVoucherRepository;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $storeId;

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

        $customers = UserRepository::getCustomers(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return ResponseFormatter::success($customers, 'Pelanggan berhasil diambil');
    }

    public function getUserVouchers(Request $request, $userId)
    {
        $limit = $request->input('limit', 5);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $activeOnly = $request->input('active_only', false);

        $vouchers = UserVoucherRepository::getUserVouchers(
            userId: $userId,
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return ResponseFormatter::success($vouchers, 'Voucher pelanggan berhasil diambil');
    }
}
