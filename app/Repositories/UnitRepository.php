<?php

namespace App\Repositories;

use App\Models\Color;
use App\Models\Size;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UnitRepository
{
    public static function getUnits(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $units = Unit::query();

        if ($storeId) {
            $units->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $units->where('name', 'like', '%' . $search . '%');
        }

        $units->orderBy($orderBy, $orderDirection);
        $units->get();

        return $units->paginate($limit);
    }

    public static function getUnitDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc')
    {
        $unitDropdown = Unit::query();

        if ($storeId) {
            $unitDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $unitDropdown->orderBy($orderBy, $orderDirection)->get();
    }
}
