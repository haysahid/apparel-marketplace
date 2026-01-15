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
            $sizes->where('name', 'ilike', '%' . $search . '%');
        }

        $sizes->orderBy($orderBy, $orderDirection);

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

    public static function isSizeExist($name, $storeId = null, $excludeId = null)
    {
        return Size::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createSize($data)
    {
        $isExists = self::isSizeExist(
            name: $data['name'],
            storeId: $data['store_id'] ?? null
        );

        if ($isExists) {
            Log::error('Ukuran dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Ukuran dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $size = new Size();
            $size->store_id = $data['store_id'] ?? null;
            $size->name = $data['name'];
            $size->save();

            DB::commit();

            return $size;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan ukuran: ' . $e);
            throw new Exception('Gagal menyimpan ukuran: ' . $e);
        }
    }

    public static function updateSize($size, $data)
    {
        $isExists = self::isSizeExist(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
            excludeId: $size->id
        );

        if ($isExists) {
            Log::error('Ukuran dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Ukuran dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $size->store_id = $data['store_id'] ?? null;
            $size->name = $data['name'];
            $size->save();

            DB::commit();

            return $size;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui ukuran: ' . $e);
            throw new Exception('Gagal memperbarui ukuran: ' . $e);
        }
    }

    public static function deleteSize(Size $size)
    {
        try {
            DB::beginTransaction();

            $size->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus ukuran: ' . $e);
            throw new Exception('Gagal menghapus ukuran: ' . $e);
        }
    }
}
