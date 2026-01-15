<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'id');
        $orderDirection = $request->input('order_direction', 'desc');
        $storeId = $request->input('store_id');

        $users = UserRepository::getUsers(
            storeId: $storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $roles = RoleRepository::getRoleDropdown(
            hideRoles: [
                'member',
                'reseller',
                'agent',
                'store-owner'
            ]
        );
        return Inertia::render('Admin/User/AddUser', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        Log::info('Create user', ['user' => $validatedData['username']]);

        UserRepository::createUser($validatedData);


        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user)
    {
        $userDetail = UserRepository::getUserDetail($user->id);
        return Inertia::render('Admin/User/UserDetail', $userDetail);
    }

    public function edit(User $user)
    {
        $roles = RoleRepository::getRoleDropdown(
            hideRoles: [
                'super-admin',
                'store-owner'
            ]
        );
        return Inertia::render('Admin/User/EditUser', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        UserRepository::updateUser(
            user: $user,
            data: $validatedData,
        );

        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        UserRepository::deleteUser($user);
        return redirect()->route('admin.user.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
