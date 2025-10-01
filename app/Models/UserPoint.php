<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPoint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'user_id',
        'current_balance',
        'lifetime_points',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
