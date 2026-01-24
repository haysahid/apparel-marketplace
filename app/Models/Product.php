<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    // Define automatic image conversions (Optimization)
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->performOnCollections('product');

        $this->addMediaConversion('preview')
            ->width(800)
            ->quality(80)
            ->performOnCollections('product'); // Automatic compression for server efficiency
    }

    protected $fillable = [
        'store_id',
        'brand_id',
        'name',
        'slug',
        'sku_prefix',
        'description',
        'discount_type',
        'discount',
        'disabled_at',
    ];

    protected $appends = [
        'lowest_base_selling_price',
        'lowest_final_selling_price',
        'highest_base_selling_price',
        'highest_final_selling_price',
        'stock_count',
        'thumbnail_url',
        'preview_url',
    ];

    // Methods
    public function getVariants()
    {
        return once(function () {
            return $this->variants()->get();
        });
    }

    // Additional attributes
    public function getLowestBaseSellingPriceAttribute()
    {
        return $this->getVariants()->min('base_selling_price');
    }

    public function getLowestFinalSellingPriceAttribute()
    {
        return $this->getVariants()->min('final_selling_price');
    }

    public function getHighestBaseSellingPriceAttribute()
    {
        return $this->getVariants()->max('base_selling_price');
    }

    public function getHighestFinalSellingPriceAttribute()
    {
        return $this->getVariants()->max('final_selling_price');
    }

    public function getStockCountAttribute()
    {
        return $this->getVariants()->sum('current_stock_level');
    }

    public function getThumbnailUrlAttribute()
    {
        $media = $this->getFirstMedia('product');
        return $media ? $media->getUrl('thumb') : null;
    }

    public function getPreviewUrlAttribute()
    {
        $media = $this->getFirstMedia('product');
        return $media ? $media->getUrl('preview') : null;
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    // public function images()
    // {
    //     return $this->morphMany(Media::class, 'model')->where('collection_name', 'images');
    // }

    public function images(): MorphMany
    {
        // Mengarahkan relasi morph ke tabel media milik Spatie
        return $this->morphMany(Media::class, 'model')
            ->where('collection_name', 'product') // Opsional: Filter koleksi tertentu
            ->orderBy('order_column', 'asc');
    }

    public function links()
    {
        return $this->hasMany(ProductLink::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
