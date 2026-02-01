<?php

namespace App\Repositories;

use App\Models\Promotion;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PromotionRepository
{
    public static function getPromotions(
        ?int $storeId,
        int $limit = 10,
        ?string $search = null,
        string $orderBy = 'created_at',
        string $orderDirection = 'desc'
    ) {
        $query = Promotion::query();

        if ($storeId) {
            $query->where('store_id', $storeId);
        } else {
            $query->whereNull('store_id');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('description', 'ilike', '%' . $search . '%');
            });
        }

        return $query->orderBy($orderBy, $orderDirection)
            ->paginate($limit);
    }

    public static function getPromotionDropdown(
        ?int $storeId,
        string $orderBy = 'name',
        string $orderDirection = 'asc'
    ) {
        $query = Promotion::query();

        if ($storeId) {
            $query->where('store_id', $storeId);
        } else {
            $query->whereNull('store_id');
        }

        return $query->orderBy($orderBy, $orderDirection)->get();
    }

    public static function createPromotion(array $data): Promotion
    {
        try {
            DB::beginTransaction();

            if (isset($data['image'])) {
                $imagePath = $data['image']->store('promotion', 'public');
                $data['image'] = $imagePath;
            }

            $data['slug'] = Str::slug($data['name']);

            $promotion = Promotion::create($data);

            DB::commit();

            return $promotion;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to create promotion: ' . $e->getMessage(), 500);
        }
    }

    public static function updatePromotion(Promotion $promotion, array $data): Promotion
    {
        try {
            DB::beginTransaction();

            if (isset($data['image'])) {
                // Delete old image if exists
                if ($promotion->image) {
                    Storage::disk('public')->delete($promotion->image);
                }

                $imagePath = $data['image']->store('promotion', 'public');
                $data['image'] = $imagePath;
            }

            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $promotion->update($data);

            DB::commit();

            return $promotion;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to update promotion: ' . $e->getMessage(), 500);
        }
    }

    public static function deletePromotion(Promotion $promotion): void
    {
        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($promotion->image) {
                Storage::disk('public')->delete($promotion->image);
            }

            $promotion->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to delete promotion: ' . $e->getMessage(), 500);
        }
    }
}
