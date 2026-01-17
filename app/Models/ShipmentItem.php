<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentItem extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'shipment_id',
        'transaction_item_id',
        'shipped_quantity',
    ];

    // Relationships
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function transaction_item()
    {
        return $this->belongsTo(TransactionItem::class);
    }
}
