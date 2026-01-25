<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionItem extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'store_id',
        'transaction_id',
        'user_id',
        'variant_id',
        'quantity',
        'unit_purchase_price',
        'unit_base_selling_price',
        'unit_discount_type',
        'unit_discount',
        'unit_final_selling_price',
        'subtotal',
        'fullfillment_status',
        'rating',
        'review',
        'reviewed_at',
    ];

    protected $appends = [
        'image'
    ];

    protected function getImageAttribute()
    {
        return $this->variant->thumbnail_url ?? null;
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
