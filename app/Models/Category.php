<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'image',
    ];

    // Additional attributes
    protected $appends = ['total_products'];

    public function getTotalProductsAttribute()
    {
        return Product::whereHas('categories', function ($query) {
            $query->where('category_id', $this->id)->where('store_id', $this->store_id);
        })->count();
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_category');
    }
}
