<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ShipmentStatus;
use App\Repositories\InvoiceRepository;
use App\Repositories\ShipmentRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private $storeId;

    public function __construct(Request $request)
    {
        $this->storeId = $request->header('X-Selected-Store-ID');
    }

    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'status' => 'required|string|in:pending,paid,processing,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['shipments', 'transaction.transaction_items'])
                ->findOrFail($validated['invoice_id']);
            $invoice->status = $validated['status'];
            $invoice->save();

            if ($validated['status'] === 'completed') {
                // Update all related shipments to 'delivered'
                $shipments = $invoice->shipments;
                foreach ($shipments as $shipment) {
                    $shipment->status = 'delivered';
                    $shipment->save();
                }

                $transaction = $invoice->transaction;

                // Update all transaction items to 'completed'
                $transactionItems = $transaction->transaction_items;
                foreach ($transactionItems as $item) {
                    $item->fullfillment_status = 'completed';
                    $item->save();
                }

                // Finally, update the transaction status to 'completed'
                $transaction->status = 'completed';
                $transaction->save();
            }

            DB::commit();

            return ResponseFormatter::success(
                $invoice,
                'Status transaksi berhasil diubah',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Change status failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'transaction_id' => $validated['transaction_id'],
            ]);

            return ResponseFormatter::error(
                'Gagal mengubah status transaksi: ' . $e->getMessage(),
                500
            );
        }
    }

    public function setDelivering(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'shipments' => 'required|array',
            'shipments.*.tracking_number' => 'required|string|max:255',
            'shipments.*.courier_name' => 'required|string|max:100',
            'shipments.*.weight' => 'nullable|numeric|min:0',
            'shipments.*.shipping_cost' => 'nullable|numeric|min:0',
            'shipments.*.items' => 'nullable|array',
            'shipments.*.items.*.transaction_item_id' => 'required|integer|exists:transaction_items,id',
            'shipments.*.items.*.shipped_quantity' => 'required|integer|min:1',
        ], [
            'invoice_id.exists' => 'Invoice dengan ID yang diberikan tidak ditemukan.',
            'shipments.*.items.*.transaction_item_id.exists' => 'Item transaksi dengan ID yang diberikan tidak ditemukan.',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($validated['invoice_id']);

            $shipmentsData = [];
            foreach ($validated['shipments'] as $shipmentData) {
                // Create shipment record
                $shipmentData = ShipmentRepository::createShipment([
                    'store_id' => $this->storeId,
                    'invoice_id' => $invoice->id,
                    'tracking_number' => $shipmentData['tracking_number'],
                    'courier_name' => $shipmentData['courier_name'],
                    'weight' => $shipmentData['weight'] ?? null,
                    'shipping_cost' => $shipmentData['shipping_cost'] ?? $invoice->shipping_cost,
                    'status' => 'in_transit',
                ]);

                // Add shipment items
                if (isset($shipmentData['items'])) {
                    foreach ($shipmentData['items'] as $itemData) {
                        ShipmentRepository::addShipmentItem(
                            shipmentId: $shipmentData->id,
                            transactionItemId: $itemData['transaction_item_id'],
                            shippedQuantity: $itemData['shipped_quantity']
                        );
                    }
                }

                $shipmentsData[] = $shipmentData;
            }

            $invoice = InvoiceRepository::updateInvoice(
                invoice: $invoice,
                data: ['status' => 'processing']
            );

            DB::commit();

            $invoice->load('shipments');

            return ResponseFormatter::success(
                $invoice,
                'Status pengiriman berhasil diubah',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Set delivering failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'invoice_id' => $validated['invoice_id'],
            ]);

            return ResponseFormatter::error(
                'Gagal mengubah status pengiriman: ' . $e->getMessage(),
                500
            );
        }
    }
}
