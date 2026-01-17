<?php

namespace App\Http\Controllers\MyStore;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Repositories\ShipmentRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShipmentController extends Controller
{
    private $storeId;

    public function __construct()
    {
        $this->storeId = session('selected_store_id');
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $orderBy = $request->input('order_by', 'created_at');
        $orderDirection = $request->input('order_direction', 'desc');

        $shipments = ShipmentRepository::getShipments(
            storeId: $this->storeId,
            limit: $limit,
            search: $search,
            orderBy: $orderBy,
            orderDirection: $orderDirection,
        );

        return Inertia::render('MyStore/Shipment/Index', [
            'shipments' => $shipments,
        ]);
    }

    public function show(Shipment $shipment)
    {
        $shipment = ShipmentRepository::getShipmentById($shipment->id);

        return Inertia::render('MyStore/Shipment/Show', [
            'shipment' => $shipment,
        ]);
    }

    public function destroy($id)
    {
        ShipmentRepository::deleteShipment($id);
        return redirect()->route('mystore.shipments.index')->with('success', 'Shipment deleted successfully.');
    }
}
