<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository
{
    public static function getAllMedia(
        $storeId = null,
        $model = null,
        $collectionName = null,
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $query = Media::query();

        if ($storeId) {
            $query->whereHasMorph(
                'model',
                [Product::class, ProductVariant::class], // Add other models as needed
                function ($q) use ($storeId) {
                    $q->where('store_id', $storeId);
                }
            );
        }

        if ($model) {
            $query->where('model_type', $model);
        }

        if ($collectionName) {
            $query->where('collection_name', $collectionName);
        }

        if ($search) {
            $query->where('file_name', 'like', "%{$search}%");
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    public static function createMedia($model, $file, $collectionName = 'default')
    {
        return $model
            ->addMedia($file)
            ->toMediaCollection($collectionName);
    }

    public static function getMediaDetail($id)
    {
        return Media::find($id);
    }

    public static function deleteMedia($id)
    {
        $media = Media::find($id);
        if ($media) {
            $media->delete();
            return true;
        }
        return false;
    }
}
