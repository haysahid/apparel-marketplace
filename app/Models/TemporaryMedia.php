<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TemporaryMedia extends Model
{
    protected $table = 'temporary_media';

    private const TMP_STORAGE_PATH = 'app/public/tmp/';

    // Also delete associated files when a record is deleted
    protected static function booted()
    {
        static::deleted(function ($temporaryMedia) {
            $storagePath = storage_path(self::TMP_STORAGE_PATH . $temporaryMedia->folder . '/' . $temporaryMedia->file_name);
            if (file_exists($storagePath)) {
                unlink($storagePath);
            }

            // Optionally, delete the folder if empty
            $folderPath = storage_path(self::TMP_STORAGE_PATH . $temporaryMedia->folder);
            if (is_dir($folderPath) && count(scandir($folderPath)) == 2) {
                rmdir($folderPath);
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
        return asset('storage/tmp/' . $this->folder . '/' . $this->file_name);
    }

    public function getIsTemporaryAttribute()
    {
        return true;
    }

    // Actions
    public function copyToMedia($model, $collectionName = 'default')
    {
        $sourcePath = storage_path(self::TMP_STORAGE_PATH . $this->folder . '/' . $this->file_name);

        if (!file_exists($sourcePath)) {
            throw new Exception("Temporary file does not exist at: {$sourcePath}");
        }

        return $model
            ->addMedia($sourcePath)
            ->usingFileName($this->file_name)
            ->toMediaCollection($collectionName);
    }

    public function getPath()
    {
        return storage_path(self::TMP_STORAGE_PATH . $this->folder . '/' . $this->file_name);
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
