<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\ShipmentRepository;
use Exception;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function dropdown(Request $request)
    {
        $invoiceId = $request->input('invoice_id');

        $shipments = ShipmentRepository::getShipmentDropdown(
            invoiceId: $invoiceId,
        );

        return ResponseFormatter::success(
            $shipments,
            'Shipment dropdown data retrieved successfully.'
        );
    }
}
