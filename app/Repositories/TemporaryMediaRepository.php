<?php

namespace App\Repositories;

use App\Models\TemporaryMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            $newFileName = self::generateNewFileName($tempMedia, $model);

            // Copy media to new model and collection
            $newMedia = $tempMedia->copyToMedia($model, $collectionName);

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

            // Delete temporary media record and file
            $tempMedia->delete();
        }
    }

    private static function generateNewFileName(TemporaryMedia $tempMedia, $model)
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
            $baseName = pathinfo($tempMedia->file_name, PATHINFO_FILENAME);
        }

        // Ensure unique file name
        $baseName .= '-' . uniqid();
        $extension = pathinfo($tempMedia->file_name, PATHINFO_EXTENSION);

        return $baseName . '.' . $extension;
    }
}
