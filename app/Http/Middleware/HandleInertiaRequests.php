<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = User::with([
            'role',
            'stores',
        ])->find($request->user()?->id);

        $selectedStore = $user?->stores->firstWhere('id', session('selected_store_id'));
        $selectedStoreRole = $selectedStore ? Role::find($selectedStore->pivot->role_id) : null;

        return [
            ...parent::share($request),
            'previous_url' => url()->previous(),
            'auth' => [
                'user' => $user,
                'is_admin' => $user && $user->isAdmin(),
                'has_store' => $user && $user->hasStoreRoles(),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'info' => session('info'),
            ],
            'setting' => function () {
                return Setting::all()->pluck('value', 'key');
            },
            'selected_store_id' => session('selected_store_id'),
            'selected_store' => $selectedStore,
            'selected_store_role' => $selectedStoreRole,
        ];
    }
}
