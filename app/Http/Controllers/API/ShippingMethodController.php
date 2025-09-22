<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\ShippingMethodRepository;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function dropdown()
    {
        $shippingMethods = ShippingMethodRepository::getShippingMethodDropdown();
        return ResponseFormatter::success($shippingMethods, 'Metode Pengiriman berhasil diambil');
    }
}
