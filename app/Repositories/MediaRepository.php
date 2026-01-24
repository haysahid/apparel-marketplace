<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
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

    public static function createMedia($model, $file, $collectionName = 'default')
    {
        return $model
            ->addMedia($file)
            ->preservingOriginal()
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

            // Rename file_name in database
            $newMedia->file_name = $newFileName;

            // Rename file on disk
            $disk = $newMedia->disk;
            $oldPath = $newMedia->getPath();
            $newPath = dirname($oldPath) . '/' . $newFileName;

            if (Storage::disk($disk)->exists($oldPath)) {
                Storage::disk($disk)->move($oldPath, $newPath);
            }

            // Update the path in the database if necessary
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

        // Ensure unique file name
        $baseName .= '-' . uniqid();
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        return $baseName . '.' . $extension;
    }
}
