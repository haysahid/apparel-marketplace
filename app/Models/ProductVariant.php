<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model
{
    use SoftDeletes, InteractsWithMedia;

    // Optional: Define automatic image conversions (Optimization)
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('preview')
            ->width(800)
            ->quality(80); // Automatic compression for server efficiency
    }

    protected $fillable = [
        'store_id',
        'product_id',
        'sku',
        'barcode',
        'slug',
        'motif',
        'color_id',
        'size_id',
        'material',
        'purchase_price',
        'base_selling_price',
        'discount_type',
        'discount',
        'final_selling_price',
        'current_stock_level',
        'last_stock_update',
        'unit_id',
        'disabled_at',
    ];

    protected $appends = [
        'name',
        'thumbnail_url',
        'preview_url',
    ];

    // Additional attributes
    protected function getNameAttribute()
    {
        $name = $this->product->name;
        if ($this->motif) {
            $name .= ' - ' . $this->motif;
        }
        if ($this->color) {
            $name .= ' - ' . $this->color->name;
        }
        if ($this->size) {
            $name .= ' - ' . $this->size->name;
        }
        return $name;
    }

    protected function getThumbnailUrlAttribute()
    {
        $firstImage = $this->images->first();
        if ($firstImage) {
            return $firstImage->thumbnail_url;
        }
        return null;
    }

    protected function getPreviewUrlAttribute()
    {
        $firstImage = $this->images->first();
        if ($firstImage) {
            return $firstImage->preview_url;
        }
        return null;
    }

    protected function url()
    {
        return route('products.show', ['slug' => $this->product->slug, 'sku' => $this->sku]);
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class)->orderBy('id', 'asc');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('order', 'asc');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
