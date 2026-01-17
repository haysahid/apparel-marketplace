<?php

namespace App\Repositories;

use App\Models\Shipment;
use App\Models\ShipmentItem;

class ShipmentRepository
{
    public static function getShipments(
        $limit = 10,
        $search = null,
        $orderBy = 'created_at',
        $orderDirection = 'desc',
        $storeId = null,
        $invoiceId = null,
    ) {
        $query = Shipment::query();
        $query->with(['invoice', 'items.transaction_item']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($invoiceId) {
            $query->where('invoice_id', $invoiceId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', '%' . $search . '%')
                    ->orWhere('courier_name', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($orderBy, $orderDirection);

        return $query->paginate($limit);
    }

    public static function getShipmentDropdown($storeId = null, $invoiceId = null)
    {
        $query = Shipment::query();
        $query->with(['items']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($invoiceId) {
            $query->where('invoice_id', $invoiceId);
        }

        return $query->get();
    }

    public static function getShipmentById($id)
    {
        return Shipment::with(['invoice', 'items.transaction_item'])->findOrFail($id);
    }

    public static function createShipment($data)
    {
        return Shipment::create($data);
    }

    public static function updateShipment($id, $data)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->update($data);
        return $shipment;
    }

    public static function deleteShipment($id)
    {
        $shipment = Shipment::findOrFail($id);
        return $shipment->delete();
    }

    public static function addShipmentItem(
        $shipmentId,
        $transactionItemId,
        $shippedQuantity,
    ) {
        return ShipmentItem::create([
            'shipment_id' => $shipmentId,
            'transaction_item_id' => $transactionItemId,
            'shipped_quantity' => $shippedQuantity,
        ]);
    }

    public static function updateShipmentItem(
        $shipmentId,
        $transactionItemId,
        $shippedQuantity,
    ) {
        $shipmentItem = ShipmentItem::where('shipment_id', $shipmentId)
            ->where('transaction_item_id', $transactionItemId)
            ->firstOrFail();

        $shipmentItem->update([
            'shipped_quantity' => $shippedQuantity,
        ]);

        return $shipmentItem;
    }

    public static function deleteShipmentItem($shipmentId, $transactionItemId)
    {
        $shipmentItem = ShipmentItem::where('shipment_id', $shipmentId)
            ->where('transaction_item_id', $transactionItemId)
            ->firstOrFail();

        return $shipmentItem->delete();
    }
}
