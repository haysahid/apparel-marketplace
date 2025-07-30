<?php

namespace App\Repositories;

use App\Models\Size;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SizeRepository
{
    public static function getSizes(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $sizes = Size::query();

        if ($storeId) {
            $sizes->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $sizes->where('name', 'like', '%' . $search . '%');
        }

        $sizes->orderBy($orderBy, $orderDirection);
        $sizes->get();

        return $sizes->paginate($limit);
    }

    public static function getSizeDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc')
    {
        $sizeDropdown = Size::query();

        if ($storeId) {
            $sizeDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $sizeDropdown->orderBy($orderBy, $orderDirection)->get();
    }
}
