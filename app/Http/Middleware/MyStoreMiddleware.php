<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyStoreMiddleware
{
    protected $user;

    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = User::find(Auth::id());
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selectedStoreId = session('selected_store_id');

        if (!$selectedStoreId) {
            if ($this->user->isAdmin()) {
                return redirect()->route('home')
                    ->with('error', 'Anda belum memilih toko. Silakan pilih toko terlebih dahulu.');
            } else {
                return redirect()->route('home');
            }
        }

        if (!$this->user->hasStoreRole($selectedStoreId, ['admin', 'super-admin', 'store-owner'])) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
