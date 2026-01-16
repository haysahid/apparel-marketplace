<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'store_id',
        'transaction_id',
        'user_id',
        'code',
        'description',
        'base_amount',
        'shipping_cost',
        'tax',
        'voucher_id',
        'voucher_amount',
        'amount',
        'due_date',
        'paid_at',
        'shipping_estimate',
        'shipped_at',
        'picked_up_at',
        'delivered_at',
        'status',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
