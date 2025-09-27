<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVoucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'unique_code',
        'usage_count',
        'redeemed_at',
        'used_at',
        'expired_at',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
