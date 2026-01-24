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
        'thumbnail_url',
        'preview_url',
    ];

    public function getOriginalUrlAttribute()
    {
        return $this->media ? $this->media->original_url : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->media ? $this->media->getUrl('thumb') : null;
    }

    public function getPreviewUrlAttribute()
    {
        return $this->media ? $this->media->getUrl('preview') : null;
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
