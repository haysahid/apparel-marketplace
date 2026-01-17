<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Enum ShipmentStatus
 *
 * Represents the various statuses that a shipment can have in the system.
 *
 * Cases:
 * - PENDING: The shipment is awaiting processing.
 * - IN_TRANSIT: The shipment is currently being transported.
 * - OUT_FOR_DELIVERY: The shipment is out for delivery to the recipient.
 * - DELIVERED: The shipment has been delivered to the recipient.
 * - RETURNED: The shipment has been returned to the sender.
 * - LOST: The shipment has been lost during transit.
 */
enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case RETURNED = 'returned';
    case LOST = 'lost';
}

class Shipment extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'store_id',
        'invoice_id',
        'tracking_number',
        'courier_name',
        'weight',
        'shipping_cost',
        'status',
    ];

    // Relationships
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
