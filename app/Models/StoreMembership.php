<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreMembership extends Model
{
    use HasFactory;

    protected $table = 'store_membership';

    protected $fillable = [
        'store_id',
        'user_id',
        'membership_type_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function membership_type()
    {
        return $this->belongsTo(MembershipType::class);
    }
}
