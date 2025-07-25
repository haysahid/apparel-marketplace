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
            $brands->where('store_id', $storeId);
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

    public static function createBrand(array $data)
    {
        try {
            DB::beginTransaction();

            $brand = Brand::create([
                'store_id' => $data['store_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'logo' => $data['logo']->store('brand', 'public'),
            ]);

            DB::commit();

            return $brand;
        } catch (Exception $e) {
            Log::error('Failed to create brand: ' . $e);
            DB::rollBack();
            throw new Exception('Gagal menyimpan brand: ' . $e);
        }
    }

    public static function updateBrand(Brand $brand, array $data)
    {
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
