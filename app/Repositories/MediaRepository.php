<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository
{
    public static function getAllMedia(
        $storeId = null,
        $models = null,
        $modelId = null,
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

        if ($models) {
            $query->whereIn('model_type', $models);
        }

        if ($modelId) {
            $query->where('model_id', $modelId);
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

    public static function createMedia(
        $model,
        $file,
        $collectionName = 'default',
        $name = null,
        $fileName = null,
        $preserveOriginal = true
    ) {
        $mediaAdder = $model->addMedia($file);
        if ($preserveOriginal) {
            $mediaAdder->preservingOriginal();
        }
        if ($name) {
            $mediaAdder->usingName($name);
        }
        if ($fileName) {
            $mediaAdder->usingFileName($fileName);
        }
        $newMedia = $mediaAdder->toMediaCollection($collectionName);

        if ($fileName === null && is_a($model, Product::class)) {
            $newFileName = self::generateNewFileName($newMedia, $model);
            $newMedia->file_name = $newFileName;
            $newMedia->save();
            return $newMedia;
        }

        return $newMedia;
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

            if (get_class($media->model) == Product::class) {
                // Delete associated product variants' media
                ProductVariantImage::where('product_id', $media->model_id)
                    ->where('media_id', $id)
                    ->delete();
            }

            return true;
        }
        return false;
    }

    public static function attachMediaToModel(
        $mediaIds,
        $model,
        $collectionName = 'default'
    ) {
        $mediaItems = Media::whereIn('id', $mediaIds)->get();
        foreach ($mediaItems as $media) {
            // If media is already attached to this model and collection, skip
            if (
                $media->model_type === get_class($model) &&
                $media->model_id === $model->id &&
                $media->collection_name === $collectionName
            ) {
                continue;
            }

            $newFileName = self::generateNewFileName($media, $model);

            // Copy media to new model and collection
            $newMedia = $media->copy($model, $collectionName);
            $newMedia->name = pathinfo($newFileName, PATHINFO_FILENAME);
            $newMedia->file_name = $newFileName;
            $newMedia->save();
        }
    }

    public static function generateNewFileName($media, $model)
    {
        // Determine new file name based on model's slug or name
        if (isset($model->slug)) {
            // Use slug if available
            $baseName = $model->slug;
        } elseif (isset($model->name)) {
            // Use name as slug
            $baseName = Str::slug($model->name);
        } else {
            // Use original
            $baseName = pathinfo($media->file_name, PATHINFO_FILENAME);
        }

        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
        return $baseName . '.' . $extension;
    }

    public static function getMediaByModelAndName($model, $name): ?Media
    {
        return Media::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('name', $name)
            ->first();
    }
}
