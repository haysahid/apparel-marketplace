<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
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
}
