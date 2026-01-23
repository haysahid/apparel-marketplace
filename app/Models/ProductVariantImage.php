<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariantImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_variant_id',
        'product_id',
        'media_id',
        'order',
    ];

    protected $appends = [
        'original_url',
    ];

    public function getOriginalUrlAttribute()
    {
        return $this->media ? $this->media->original_url : null;
    }

    // Relationships
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
