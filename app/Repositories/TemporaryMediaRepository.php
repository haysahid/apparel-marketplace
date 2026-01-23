<?php

namespace App\Repositories;

use App\Models\TemporaryMedia;

class TemporaryMediaRepository
{
    public static function getAllTemporaryMedia(
        $storeId = null,
        $limit = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
    ) {
        $query = TemporaryMedia::query();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $query->orderBy($orderBy, $orderDirection);

        if ($limit) {
            return $query->limit($limit)->get();
        }

        return $query->get();
    }

    public static function createTemporaryMedia(
        $file,
        $storeId = null,
    ) {
        $folder = uniqid() . '-' . now()->timestamp;

        $filename = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $size = $file->getSize();

        // Save to storage/app/tmp
        $file->storeAs('tmp/' . $folder, $filename);

        return TemporaryMedia::create([
            'store_id' => $storeId,
            'folder' => $folder,
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'file_name' => $filename,
            'mime_type' => $mimeType,
            'size' => $size,
        ]);
    }

    public static function getTemporaryMedia($id)
    {
        return TemporaryMedia::find($id);
    }

    public static function deleteTemporaryMedia($id)
    {
        $tempMedia = TemporaryMedia::find($id);
        if ($tempMedia) {
            $tempMedia->delete();

            return true;
        }
        return false;
    }

    public static function attachTemporaryMediaToModel(
        $temporaryMediaIds,
        $model,
        $collectionName = 'default'
    ) {
        $temporaryMediaItems = TemporaryMedia::whereIn('id', $temporaryMediaIds)->get();
        foreach ($temporaryMediaItems as $tempMedia) {
            $tempMedia->copy($model, $collectionName);
            $tempMedia->delete();
        }
    }
}
