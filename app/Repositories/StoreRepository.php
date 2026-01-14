<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStoreRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StoreRepository
{
    protected $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function getStoreInfo()
    {
        return $this->store->load(['advantages', 'social_links']);
    }

    public static function createStore(
        $data,
        $advantages = null,
        $socialLinks = null,
        $logo = null,
        $banner = null,
    ) {
        try {
            DB::beginTransaction();

            // Create store
            $store = Store::create($data);

            // Handle logo upload
            if (isset($logo)) {
                $store->logo = $logo->store('store', 'public');
            }

            // Handle banner upload
            if (isset($banner)) {
                $store->banner = $banner->store('store', 'public');
            }

            if (isset($store->logo) || isset($store->banner)) {
                $store->save();
            }

            // Create user store relationship
            UserStoreRole::create([
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'role_id' => Role::where('slug', 'store-owner')->first()->id,
            ]);

            // Auto assign super-admin and admin users to the store being created
            $users = User::whereHas('role', function ($query) {
                $query->whereIn('slug', ['super-admin', 'admin']);
            })->get();

            foreach ($users as $user) {
                UserStoreRole::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'store_id' => $store->id,
                    ],
                    [
                        'role_id' => $user->role->id,
                    ]
                );
            }

            // Create advantages
            if ($advantages === null) {
                $advantages = [[], [], [], []];
            }

            foreach ($advantages as $advantage) {
                $store->advantages()->create([
                    'name' => $advantage['name'] ?? null,
                    'description' => $advantage['description'] ?? null,
                ]);
            }

            // Create social links
            if ($socialLinks === null) {
                $socialLinks = [
                    [
                        'name' => 'Instagram',
                        'url' => null,
                        'icon' => 'icon/ic_instagram.svg',
                    ],
                    [
                        'name' => 'Facebook',
                        'url' => null,
                        'icon' => 'icon/ic_facebook.svg',
                    ],
                    [
                        'name' => 'TikTok',
                        'url' => null,
                        'icon' => 'icon/ic_tiktok.svg',
                    ],
                ];
            }

            foreach ($socialLinks as $link) {
                $store->social_links()->create([
                    'store_id' => $store->id,
                    'name' => $link['name'] ?? null,
                    'url' => $link['url'],
                    'icon' => $link['icon'] ?? null,
                ]);
            }

            DB::commit();
            return $store;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal membuat toko: ' . $e->getMessage());
        }
    }

    public function updateStoreInfo(
        $data,
        $advantages,
        $socialLinks,
        $logo = null,
        $banner = null,
    ) {
        try {
            DB::beginTransaction();

            $store = Store::with(['advantages', 'social_links'])->find($this->store->id);

            // Update logo if provided
            if (isset($logo)) {
                // Delete old logo if exists
                if ($store->logo) {
                    Storage::disk('public')->delete($store->logo);
                }

                $store->logo = $logo->store('store', 'public');
            }

            if (isset($banner)) {
                // Delete old banner if exists
                if ($store->banner) {
                    Storage::disk('public')->delete($store->banner);
                }

                $store->banner = $banner->store('store', 'public');
            }

            $store->update($data);

            // Update advantages
            if ($advantages) {
                $currentAdvantages = $store->advantages->pluck('id')->toArray();
                foreach ($advantages as $advantage) {
                    if (isset($advantage['id']) && in_array($advantage['id'], $currentAdvantages)) {
                        // Update existing advantage
                        $store->advantages()->where('id', $advantage['id'])->update([
                            'name' => $advantage['name'],
                            'description' => $advantage['description'] ?? null,
                        ]);
                    } else {
                        // Create new advantage
                        $store->advantages()->create([
                            'store_id' => $store->id,
                            'name' => $advantage['name'],
                            'description' => $advantage['description'] ?? null,
                        ]);
                    }
                }
            }

            // Update social links
            if ($socialLinks) {
                $currentLinks = $store->social_links->pluck('id')->toArray();
                foreach ($socialLinks as $link) {
                    if (isset($link['id']) && in_array($link['id'], $currentLinks)) {
                        // Update existing social link
                        $store->social_links()->where('id', $link['id'])->update([
                            'url' => $link['url'],
                        ]);
                    }
                }
            }

            DB::commit();
            return $store;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Gagal memperbarui informasi toko: ' . $e->getMessage());
        }
    }

    public static function getStores(
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $userId = null,
    ) {
        $query = Store::query();

        if ($userId) {
            $query->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->whereHas('store_roles', function ($roleQuery) {
                        $roleQuery->whereIn('slug', ['store-owner']);
                    });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    public static function getStoreDetail($storeId)
    {
        $store = Store::with([
            'advantages',
            'social_links',
            'users',
            'store_roles'
        ])->find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan');
        }

        $store->user_role_pairs = $store->getUserRolePairsAttribute();
        // $store->makeHidden(['users', 'store_roles']);

        $storeStats = DB::table('stores')
            ->leftJoin('products', 'stores.id', '=', 'products.store_id')
            ->leftJoin('invoices', 'stores.id', '=', 'invoices.store_id')
            ->where('stores.id', $storeId)
            ->selectRaw('
                stores.id,
                COUNT(DISTINCT products.id) as count_products,
                COUNT(DISTINCT invoices.id) as count_orders,
                COALESCE(SUM(invoices.amount), 0) as count_revenue
            ')
            ->groupBy('stores.id')
            ->first();

        $countProducts = (int) $storeStats->count_products ?? 0;
        $countOrders = (int) $storeStats->count_orders ?? 0;
        $countRevenue = (int) $storeStats->count_revenue ?? 0;

        return [
            'store' => $store,
            'count_products' => $countProducts,
            'count_orders' => $countOrders,
            'count_revenue' => $countRevenue,
        ];
    }

    public static function addUserRole($storeId, $userId, $roleSlug)
    {
        $role = Role::where('slug', $roleSlug)->first();

        if (!$role) {
            throw new Exception('Role tidak ditemukan', 404);
        }

        $roleId = $role->id;

        // Check if the user is already assigned to the store
        $existingAssignment = UserStoreRole::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->first();

        if ($existingAssignment) {
            throw new Exception('Pengguna sudah ditugaskan ke toko ini', 400);
        }

        // Assign role
        $userStoreRole = UserStoreRole::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        return $userStoreRole;
    }

    public static function updateUserRole($storeId, $userId, $roleSlug)
    {
        $loggedInUser = User::with(['role'])->find(Auth::id());

        $role = Role::where('slug', $roleSlug)->first();

        if (!$role) {
            throw new Exception('Role tidak ditemukan', 404);
        }

        $roleId = $role->id;

        // Check if the user is the store owner
        $ownerRole = Role::where('slug', 'store-owner')->first();
        $isOwner = UserStoreRole::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->where('role_id', $ownerRole->id)
            ->exists();

        if ($isOwner && !$loggedInUser->isAdmin()) {
            throw new Exception('Tidak dapat mengubah peran pemilik toko', 403);
        }

        // Update role
        $userStoreRole = UserStoreRole::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->first();

        Log::info('UserStoreRole found:', [
            'user_id' => $userId,
            'store_id' => $storeId,
            'role_id' => $roleSlug,
        ]);

        if (!$userStoreRole) {
            throw new Exception('Pengguna tidak ditemukan di toko ini', 404);
        }

        $userStoreRole->role_id = $roleId;
        $userStoreRole->save();

        return $userStoreRole;
    }

    public static function removeUserRole($storeId, $userId)
    {
        $loggedInUser = User::with(['role'])->find(Auth::id());

        // Check if the user is the store owner
        $ownerRole = Role::where('slug', 'store-owner')->first();
        $isOwner = UserStoreRole::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->where('role_id', $ownerRole->id)
            ->exists();

        if ($isOwner && !$loggedInUser->isAdmin()) {
            throw new Exception('Tidak dapat menghapus peran pemilik toko', 403);
        }

        // Remove role
        $userStoreRole = UserStoreRole::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->first();

        if (!$userStoreRole) {
            throw new Exception('Pengguna tidak ditemukan di toko ini', 404);
        }

        $userStoreRole->delete();

        return true;
    }

    public static function addStoreLogo($storeId, $file)
    {
        $store = Store::find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan', 404);
        }

        $logoPath = $file->store('store', 'public');

        if (!$logoPath) {
            throw new Exception('Gagal mengunggah logo toko', 500);
        }

        $store->logo = $logoPath;
        $store->save();
    }

    public static function updateStoreLogo($storeId, $file)
    {
        $store = Store::find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan', 404);
        }

        // Delete old logo if exists
        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
        }

        $logoPath = $file->store('store', 'public');

        if (!$logoPath) {
            throw new Exception('Gagal mengunggah logo toko', 500);
        }

        $store->logo = $logoPath;
        $store->save();

        return $logoPath;
    }

    public static function deleteStoreLogo($storeId)
    {
        $store = Store::find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan', 404);
        }

        // Delete old logo if exists
        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
            $store->logo = null;
            $store->save();
        }

        return true;
    }

    public static function addStoreBanner($storeId, $file)
    {
        $store = Store::find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan', 404);
        }

        $bannerPath = $file->store('store', 'public');

        if (!$bannerPath) {
            throw new Exception('Gagal mengunggah banner toko', 500);
        }

        $store->banner = $bannerPath;
        $store->save();
    }

    public static function updateStoreBanner($storeId, $file)
    {
        $store = Store::find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan', 404);
        }

        // Delete old banner if exists
        if ($store->banner) {
            Storage::disk('public')->delete($store->banner);
        }

        $bannerPath = $file->store('store', 'public');

        if (!$bannerPath) {
            throw new Exception('Gagal mengunggah banner toko', 500);
        }

        $store->banner = $bannerPath;
        $store->save();

        return $bannerPath;
    }

    public static function deleteStoreBanner($storeId)
    {
        $store = Store::find($storeId);

        if (!$store) {
            throw new Exception('Toko tidak ditemukan', 404);
        }

        // Delete old banner if exists
        if ($store->banner) {
            Storage::disk('public')->delete($store->banner);
            $store->banner = null;
            $store->save();
        }

        return true;
    }
}
