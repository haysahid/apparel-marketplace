<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TemporaryMedia extends Model
{
    protected $table = 'temporary_media';

    private const TMP_STORAGE_PATH = 'app/public/tmp/';

    // Also delete associated files when a record is deleted
    protected static function booted()
    {
        static::deleted(function ($temporaryMedia) {
            $path = self::TMP_STORAGE_PATH;
            if (!empty($temporaryMedia->folder)) {
                $path .= $temporaryMedia->folder . '/';
            }

            $storagePath = storage_path($path . $temporaryMedia->file_name);
            if (file_exists($storagePath)) {
                unlink($storagePath);
            }
        });
    }

    protected $fillable = [
        'store_id',
        'folder',
        'name',
        'file_name',
        'mime_type',
        'size',
    ];

    protected $appends = [
        'original_url',
        'is_temporary',
    ];

    public function getOriginalUrlAttribute()
    {
        $path = 'tmp/';
        if (!empty($this->folder)) {
            $path .= $this->folder . '/';
        }
        return asset('storage/' . $path . $this->file_name);
    }

    public function getIsTemporaryAttribute()
    {
        return true;
    }

    // Actions
    public function copyToMedia($model, $collectionName = 'default'): Media
    {
        $path = self::TMP_STORAGE_PATH;
        if (!empty($this->folder)) {
            $path .= $this->folder . '/';
        }

        $sourcePath = storage_path($path . $this->file_name);

        if (!file_exists($sourcePath)) {
            throw new Exception("Temporary file does not exist at: {$sourcePath}");
        }

        return $model
            ->addMedia($sourcePath)
            ->preservingOriginal()
            ->usingFileName($this->file_name)
            ->toMediaCollection($collectionName);
    }

    public function getPath()
    {
        $path = self::TMP_STORAGE_PATH;
        if (!empty($this->folder)) {
            $path .= $this->folder . '/';
        }

        return storage_path($path . $this->file_name);
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
