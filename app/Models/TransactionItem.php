<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'transaction_id',
        'variant_id',
        'quantity',
        'unit_base_price',
        'unit_discount_type',
        'unit_discount',
        'unit_final_price',
        'subtotal',
        'fullfillment_status',
    ];

    protected $appends = [
        'image'
    ];

    protected function getImageAttribute()
    {
        return $this->variant->images[0]->image ?? $this->variant->product->images[0]->image ?? null;
    }

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
