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
        'last_used_at',
        'expired_at',
    ];

    // Additional attributes
    protected $appends = [
        'status',
    ];

    public function getStatusAttribute()
    {
        if ($this->last_used_at) {
            return 'used';
        }

        if ($this->expired_at && $this->expired_at < now()) {
            return 'expired';
        }

        return 'active';
    }

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
