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

    public static function isCategoryExists($name, $storeId = null, $excludeId = null)
    {
        return Category::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createCategory($data)
    {
        $isExists = self::isCategoryExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null
        );

        if ($isExists) {
            Log::error('Kategori dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Kategori dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $category = new Category();
            $category->store_id = $data['store_id'] ?? null;
            $category->name = $data['name'];

            if (isset($data['image'])) {
                $category->image = $data['image']->store('category');
            }

            $category->save();

            DB::commit();

            return $category;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan kategori: ' . $e);
            throw new Exception('Gagal menyimpan kategori: ' . $e);
        }
    }

    public static function updateCategory(Category $category, $data)
    {
        $isExists = self::isCategoryExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
            excludeId: $category->id
        );

        if ($isExists) {
            Log::error('Kategori dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Kategori dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $category->name = $data['name'];

            if (isset($data['image'])) {
                if ($category->image) {
                    Storage::delete($category->image);
                }
                $category->image = $data['image']->store('category');
            }

            $category->save();

            DB::commit();

            return $category;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui kategori: ' . $e);
            throw new Exception('Gagal memperbarui kategori: ' . $e);
        }
    }

    public static function deleteCategory(Category $category)
    {
        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($category->image) {
                Storage::delete($category->image);
            }

            $category->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus kategori: ' . $e);
            throw new Exception('Gagal menghapus kategori: ' . $e);
        }
    }
}
