<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipType extends Model
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
    ];
}
