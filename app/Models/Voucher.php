<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'code',
        'description',
        'type',
        'amount',
        'min_amount',
        'max_amount',
        'redeem_start_date',
        'redeem_end_date',
        'usage_duration_days',
        'usage_start_date',
        'usage_end_date',
        'usage_limit',
        'required_points',
        'usage_url',
        'is_public',
        'is_internal',
        'partner_id',
        'disabled_at',
    ];

    // Casts
    protected $casts = [
        'is_public' => 'boolean',
        'is_internal' => 'boolean',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function user_vouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vouchers')->withTimestamps()->withPivot('usage_count');
    }
}
