<?php

namespace App\Repositories;

use App\Models\Brand;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BrandRepository
{
    public static function getBrands(
        $storeId = null,
        $limit = 5,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $brands = Brand::query();

        if ($storeId) {
            $brands->where(function ($query) use ($storeId) {
                $query->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        if ($search) {
            $brands->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $brands->orderBy($orderBy, $orderDirection);
        $brands->get();

        return $brands->paginate($limit);
    }

    public static function getBrandDropdown($storeId = null, $orderBy = 'name', $orderDirection = 'asc')
    {
        $brandDropdown = Brand::query();

        if ($storeId) {
            $brandDropdown->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)
                    ->orWhereNull('store_id');
            });
        }

        return $brandDropdown->orderBy($orderBy, $orderDirection)->get();
    }

    public static function isBrandExists($name, $storeId = null, $excludeId = null)
    {
        return Brand::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createBrand(array $data)
    {
        $isExists = self::isBrandExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
        );

        if ($isExists) {
            Log::error('Brand dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Brand dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $brand = Brand::create([
                'store_id' => $data['store_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'logo' => $data['logo'] ? $data['logo']->store('brand', 'public') : null,
            ]);

            DB::commit();

            return $brand;
        } catch (Exception $e) {
            Log::error('Failed to create brand: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menyimpan brand: ' . $e->getMessage());
        }
    }

    public static function updateBrand(Brand $brand, array $data)
    {
        $isExists = self::isBrandExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
            excludeId: $brand->id,
        );

        if ($isExists) {
            Log::error('Brand dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Brand dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $brand->name = $data['name'];
            $brand->description = $data['description'] ?? null;

            if (isset($data['logo'])) {
                // Delete old logo if exists
                if ($brand->logo) {
                    Storage::disk('public')->delete($brand->logo);
                }
                $brand->logo = $data['logo']->store('brand', 'public');
            }

            $brand->save();

            DB::commit();

            return $brand;
        } catch (Exception $e) {
            Log::error('Failed to update brand: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal memperbarui brand: ' . $e);
        }
    }

    public static function deleteBrand(Brand $brand)
    {
        try {
            DB::beginTransaction();

            // Delete the brand logo if it exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $brand->delete();

            DB::commit();
        } catch (Exception $e) {
            Log::error('Failed to delete brand: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menghapus brand: ' . $e);
        }
    }
}
