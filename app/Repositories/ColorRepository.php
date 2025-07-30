<?php

namespace App\Repositories;

use App\Models\Color;
use App\Models\Size;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ColorRepository
{
    public static function getColors(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $colors = Color::query();

        if ($storeId) {
            $colors->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $colors->where('name', 'like', '%' . $search . '%');
        }

        $colors->orderBy($orderBy, $orderDirection);
        $colors->get();

        return $colors->paginate($limit);
    }

    public static function getColorDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc')
    {
        $colorDropdown = Color::query();

        if ($storeId) {
            $colorDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $colorDropdown->orderBy($orderBy, $orderDirection)->get();
    }
}
