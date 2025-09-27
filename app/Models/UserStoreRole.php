<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStoreRole extends Model
{
    protected $table = 'user_store_role';

    protected $fillable = [
        'user_id',
        'store_id',
        'role_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isOwner()
    {
        return $this->role->slug === 'store-owner';
    }
}
