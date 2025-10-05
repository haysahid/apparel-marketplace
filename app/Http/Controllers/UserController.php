<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    public function login()
    {
        return Inertia::render('Auth/Login');
    }

    public function loginProcess(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::id());
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

            // if ($user->isAdmin()) {
            //     return redirect()->route('admin.dashboard')->with([
            //         'success' => 'Berhasil masuk sebagai admin.',
            //         'access_token' => $accessToken,
            //     ]);
            // }

            return redirect()->route('home')->with([
                'success' => 'Berhasil masuk.',
            ]);
        }

        return redirect()->back()->withErrors([
            'access' => 'Username atau password salah.',
        ]);
    }

    public function register()
    {
        return Inertia::render('Auth/Register');
    }

    public function registerProcess(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap harus diisi.',
            'username.required' => 'Username harus diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.string' => 'Format nomor telepon tidak valid.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password harus minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = UserRepository::register($data);

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

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with([
                'success' => 'Berhasil masuk sebagai admin.',
            ]);
        }

        return redirect()->route('home')->with([
            'success' => 'Akun berhasil dibuat. Selamat datang!',
        ]);
    }

    public function profile()
    {
        $store = Store::with([
            'advantages',
            'certificates' => function ($query) {
                $query->limit(5);
            },
            'social_links',
        ])->first();

        $user = User::find(Auth::id());

        return Inertia::render('Auth/Profile', [
            'user' => $user,
            'store' => $store,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
        ], [
            'name.required' => 'Nama lengkap harus diisi.',
            'username.required' => 'Username harus diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.string' => 'Format nomor telepon tidak valid.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter.',
        ]);

        $user->update($data);

        return redirect()->back()->with([
            'success' => 'Profil berhasil diperbarui.',
        ]);
    }

    public function logout()
    {
        session()->forget('selected_store_id');
        Cookie::queue(Cookie::forget('access_token'));
        Cookie::queue(Cookie::forget('selected_store_id'));
        Auth::logout();
        return redirect()->route('home')->with([
            'success' => 'Berhasil keluar.',
        ]);
    }
}
