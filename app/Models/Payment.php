<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}

class Payment extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'transaction_id',
        'payment_method_id',
        'amount',
        'note',
        'reason',
        'image',
        'midtrans_snap_token',
        'midtrans_response',
        'status',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
    ];

    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
