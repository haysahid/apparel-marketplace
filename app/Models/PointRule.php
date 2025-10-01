<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'description',
        'type',
        'min_spend',
        'points_earned',
        'conversion_rate',
        'valid_from',
        'valid_until',
        'disabled_at',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }
}
