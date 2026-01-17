<?php

namespace App\Http\Controllers\MyStore\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\ShipmentRepository;
use Exception;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    private $storeId;

    public function __construct(Request $request)
    {
        $this->storeId = $request->header('X-Selected-Store-ID');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');
        $invoiceId = $request->input('invoice_id');

        $shipments = ShipmentRepository::getShipments(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
            invoiceId: $invoiceId,
        );

        return ResponseFormatter::success(
            $shipments,
            'Shipments retrieved successfully.'
        );
    }

    public function dropdown(Request $request)
    {
        $invoiceId = $request->input('invoice_id');

        $shipments = ShipmentRepository::getShipmentDropdown(
            storeId: $this->storeId,
            invoiceId: $invoiceId,
        );

        return ResponseFormatter::success(
            $shipments,
            'Shipment dropdown data retrieved successfully.'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'invoice_id' => 'required|integer|exists:invoices,id',
            'tracking_number' => 'required|string|max:255',
            'courier_name' => 'required|string|max:255',
            'weight' => 'nullable|integer|min:0',
            'shipping_cost' => 'required|integer|min:0',
        ], [
            'store_id.required' => 'ID toko wajib diisi.',
            'store_id.integer' => 'ID toko harus berupa angka.',
            'store_id.exists' => 'ID toko tidak ditemukan.',
            'invoice_id.required' => 'ID invoice wajib diisi.',
            'invoice_id.integer' => 'ID invoice harus berupa angka.',
            'invoice_id.exists' => 'ID invoice tidak ditemukan.',
            'tracking_number.required' => 'Nomor resi wajib diisi.',
            'tracking_number.string' => 'Nomor resi harus berupa teks.',
            'tracking_number.max' => 'Nomor resi maksimal 255 karakter.',
            'courier_name.required' => 'Nama kurir wajib diisi.',
            'courier_name.string' => 'Nama kurir harus berupa teks.',
            'courier_name.max' => 'Nama kurir maksimal 255 karakter.',
            'weight.integer' => 'Berat harus berupa angka.',
            'weight.min' => 'Berat minimal 0.',
            'shipping_cost.required' => 'Biaya pengiriman wajib diisi.',
            'shipping_cost.integer' => 'Biaya pengiriman harus berupa angka.',
            'shipping_cost.min' => 'Biaya pengiriman minimal 0.',
        ]);

        try {
            $shipment = ShipmentRepository::createShipment($validated);

            return ResponseFormatter::success(
                $shipment,
                'Shipment created successfully.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Failed to create shipment: ' . $e->getMessage(),
                500
            );
        }
    }

    public function show($id)
    {
        $shipment = ShipmentRepository::getShipmentById($id);

        return ResponseFormatter::success(
            $shipment,
            'Shipment details retrieved successfully.'
        );
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'tracking_number' => 'sometimes|required|string|max:255',
            'courier_name' => 'sometimes|required|string|max:255',
            'weight' => 'sometimes|nullable|integer|min:0',
            'shipping_cost' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|required|in:pending,in_transit,out_for_delivery,delivered,returned,lost',
        ], [
            'tracking_number.required' => 'Nomor resi wajib diisi.',
            'tracking_number.string' => 'Nomor resi harus berupa teks.',
            'tracking_number.max' => 'Nomor resi maksimal 255 karakter.',
            'courier_name.required' => 'Nama kurir wajib diisi.',
            'courier_name.string' => 'Nama kurir harus berupa teks.',
            'courier_name.max' => 'Nama kurir maksimal 255 karakter.',
            'weight.integer' => 'Berat harus berupa angka.',
            'weight.min' => 'Berat minimal 0.',
            'shipping_cost.required' => 'Biaya pengiriman wajib diisi.',
            'shipping_cost.integer' => 'Biaya pengiriman harus berupa angka.',
            'shipping_cost.min' => 'Biaya pengiriman minimal 0.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status tidak valid.',
        ]);

        try {
            $shipment = ShipmentRepository::updateShipment($id, $validated);

            return ResponseFormatter::success(
                $shipment,
                'Shipment updated successfully.'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                'Failed to update shipment: ' . $e->getMessage(),
                500
            );
        }
    }

    public function destroy($id)
    {
        ShipmentRepository::deleteShipment($id);
        return ResponseFormatter::success(
            null,
            'Shipment deleted successfully.'
        );
    }
}
