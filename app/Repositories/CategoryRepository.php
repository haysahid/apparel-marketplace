<?php

namespace App\Repositories;

use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryRepository
{
    public static function getCategories(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $categories = Category::query();

        if ($storeId) {
            $categories->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $categories->where('name', 'like', '%' . $search . '%');
        }

        $categories->orderBy($orderBy, $orderDirection);
        $categories->get();

        return $categories->paginate($limit);
    }

    public static function getCategoryDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc')
    {
        $categoryDropdown = Category::query();

        if ($storeId) {
            $categoryDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $categoryDropdown->orderBy($orderBy, $orderDirection)->get();
    }
}
