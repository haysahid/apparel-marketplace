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

    public static function isUnitExists($name, $storeId = null, $excludeId = null)
    {
        return Unit::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createUnit($data)
    {
        $isExists = self::isUnitExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null
        );

        if ($isExists) {
            Log::error('Unit dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Unit dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $unit = new Unit();
            $unit->store_id = $data['store_id'] ?? null;
            $unit->name = $data['name'];
            $unit->save();

            DB::commit();

            return $unit;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating unit: ' . $e);
            throw new Exception('Gagal membuat unit: ' . $e);
        }
    }

    public static function updateUnit(Unit $unit, $data)
    {
        $isExists = self::isUnitExists(
            name: $data['name'],
            storeId: $unit->store_id,
            excludeId: $unit->id
        );

        if ($isExists) {
            Log::error('Unit dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Unit dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $unit->name = $data['name'];
            $unit->save();

            DB::commit();

            return $unit;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating unit: ' . $e);
            throw new Exception('Gagal memperbarui unit: ' . $e);
        }
    }

    public static function deleteUnit(Unit $unit)
    {
        try {
            DB::beginTransaction();

            $unit->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting unit: ' . $e);
            throw new Exception('Gagal menghapus unit: ' . $e);
        }
    }
}
