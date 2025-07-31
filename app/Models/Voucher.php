<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'type',
        'amount',
        'min_amount',
        'max_amount',
        'start_date',
        'end_date',
        'usage_limit',
        'disabled_at',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
