<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\Store;
use App\Models\UserStoreRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    ) {
        try {
            DB::beginTransaction();

            // Create store
            $store = Store::create($data);

            // Create user store relationship
            UserStoreRole::create([
                'store_id' => $store->id,
                'user_id' => Auth::id(),
                'role_id' => Role::where('slug', 'store-owner')->first()->id,
            ]);

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
    ) {
        try {
            DB::beginTransaction();

            $store = Store::with(['advantages', 'social_links'])->find($this->store->id);

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
    ) {
        $query = Store::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
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
}
