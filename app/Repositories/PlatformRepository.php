<?php

namespace App\Repositories;

use App\Models\Platform;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PlatformRepository
{
    public static function getPlatforms(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $platforms = Platform::query();

        if ($storeId) {
            $platforms->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $platforms->where('name', 'like', '%' . $search . '%');
        }

        $platforms->orderBy($orderBy, $orderDirection);
        $platforms->get();

        return $platforms->paginate($limit);
    }

    public static function getPlatformDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc')
    {
        $platformDropdown = Platform::query();

        if ($storeId) {
            $platformDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $platformDropdown->orderBy($orderBy, $orderDirection)->get();
    }
}
