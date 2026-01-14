<?php

namespace App\Http\Controllers\Admin\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\InvoiceRepository;
use App\Repositories\StoreRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $users = UserRepository::getUsers(
            storeId: null,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return ResponseFormatter::success($users, 'Pengguna berhasil diambil.');
    }

    public function dropdown(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $users = UserRepository::getUserDropdown(
            search: $search,
            limit: $limit
        );

        return ResponseFormatter::success(
            $users,
            'User dropdown retrieved successfully.'
        );
    }

    public function getUserPointTransactions(Request $request, $userId)
    {
        $limit = $request->input('limit', 5);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $type = $request->input('type');

        $pointTransactions = UserRepository::getUserPointTransactions(
            userId: $userId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            type: $type,
        );

        return ResponseFormatter::success($pointTransactions, 'Transaksi poin pengguna berhasil diambil.');
    }

    public function getUserVouchers(Request $request, $userId)
    {
        $limit = $request->input('limit', 5);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $activeOnly = $request->input('active_only', false);
        $storeId = $request->input('store_id');

        $vouchers = UserRepository::getUserVouchers(
            userId: $userId,
            storeId: $storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return ResponseFormatter::success($vouchers, 'Voucher pelanggan berhasil diambil');
    }
}
