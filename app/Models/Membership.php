<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'group',
        'name',
        'alias',
        'level',
        'slug',
        'description',
        'item_discount_percentage',
        'shipping_discount_percentage',
        'min_purchase_amount',
        'hex_code_bg',
        'hex_code_text',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'store_memberships', 'membership_id', 'user_id')
            ->withTimestamps();
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_memberships', 'membership_id', 'store_id')
            ->withTimestamps();
    }

    public function store_memberships()
    {
        return $this->hasMany(StoreMembership::class, 'membership_id');
    }
}
