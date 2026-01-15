<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreMembership extends Model
{
    protected $table = 'store_membership';

    protected $fillable = [
        'store_id',
        'user_id',
        'membership_type_id',
    ];
}
