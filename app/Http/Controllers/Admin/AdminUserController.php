<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $storeId = $request->input('store_id');

        $users = UserRepository::getUsers(
            storeId: $storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('Admin/User', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        $userDetail = UserRepository::getUserDetail($user->id);
        return Inertia::render('Admin/User/UserDetail', $userDetail);
    }
}
