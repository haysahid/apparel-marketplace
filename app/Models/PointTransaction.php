<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'user_id',
        'type',
        'description',
        'points_amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'transaction_id',
        'voucher_id',
        'admin_id',
        'point_rule_id',
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

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function point_rule()
    {
        return $this->belongsTo(PointRule::class);
    }
}
