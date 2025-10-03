<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_store_role')->withTimestamps()->withPivot('store_id');
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'user_store_role')->withTimestamps()->withPivot('user_id');
    }
}
