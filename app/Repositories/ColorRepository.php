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
            $colors->where(
                function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('hex_code', 'like', '%' . $search . '%');
                }
            );
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

    public static function isColorExists($name, $storeId = null, $excludeId = null)
    {
        return Color::where('name', $name)
            ->where('store_id', $storeId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    public static function createColor($data)
    {
        $isExists = self::isColorExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null
        );

        if ($isExists) {
            Log::error('Warna dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Warna dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $color = new Color();
            $color->store_id = $data['store_id'] ?? null;
            $color->name = $data['name'];
            $color->hex_code = $data['hex_code'];

            if (isset($data['image'])) {
                $color->image = Storage::putFile('colors', $data['image']);
            }

            $color->save();

            DB::commit();

            return $color;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan warna: ' . $e);
            throw new Exception('Gagal menyimpan warna: ' . $e);
        }
    }

    public static function updateColor(Color $color, $data)
    {
        $isExists = self::isColorExists(
            name: $data['name'],
            storeId: $data['store_id'] ?? null,
            excludeId: $color->id
        );

        if ($isExists) {
            Log::error('Warna dengan nama ini sudah ada: ' . $data['name']);
            throw new Exception('Warna dengan nama ini sudah ada.', 409);
        }

        try {
            DB::beginTransaction();

            $color->name = $data['name'];
            $color->hex_code = $data['hex_code'];
            $color->save();

            DB::commit();

            return $color;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal memperbarui warna: ' . $e);
            throw new Exception('Gagal memperbarui warna: ' . $e);
        }
    }

    public static function deleteColor(Color $color)
    {
        try {
            DB::beginTransaction();
            $color->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal menghapus warna: ' . $e);
            throw new Exception('Gagal menghapus warna: ' . $e);
        }
    }
}
