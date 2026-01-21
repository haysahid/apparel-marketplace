<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemporaryMedia extends Model
{
    protected $table = 'temporary_media';

    protected $fillable = [
        'store_id',
        'folder',
        'name',
        'file_name',
        'mime_type',
        'size',
    ];

    protected $appends = [
        'url',
        'is_temporary',
    ];

    public function getUrlAttribute()
    {
        return asset('storage/tmp/' . $this->folder . '/' . $this->file_name);
    }

    public function getIsTemporaryAttribute()
    {
        return true;
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
