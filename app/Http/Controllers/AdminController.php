<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function login()
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function loginProcess(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::id());

            if (!$user->isAdmin()) {
                Auth::logout();
                return redirect()->route('admin.login')->withErrors([
                    'access' => 'Anda tidak memiliki akses ke halaman ini.',
                ]);
            }

            $accessToken = $user->createToken('authToken')->plainTextToken;

            Cookie::queue(Cookie::forever(
                name: 'access_token',
                value: $accessToken,
                secure: false,
                httpOnly: false,
            ));

            $redirectUrl = $request->input('redirect');

            if ($redirectUrl) {
                return redirect()->to($redirectUrl)->with([
                    'success' => 'Berhasil masuk.',
                ]);
            }

            return redirect()->route('admin.dashboard')
                ->with([
                    'success' => 'Berhasil masuk sebagai admin.',
                    'access_token' => $accessToken,
                ]);
        }

        return redirect()->back()->withErrors([
            'access' => 'Username atau password salah.',
        ]);
    }

    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    public function dashboard()
    {
        $productCount = Product::count();
        $userCount = User::count();

        return Inertia::render(
            'Admin/Dashboard',
            [
                'productCount' => $productCount,
                'userCount' => $userCount,
            ]
        );
    }

    public function logout()
    {
        session()->forget('selected_store_id');
        Cookie::queue(Cookie::forget('access_token'));
        Cookie::queue(Cookie::forget('selected_store_id'));
        Auth::logout();
        return redirect()->route('admin.login')->with([
            'success' => 'Berhasil keluar.',
        ]);
    }
}
