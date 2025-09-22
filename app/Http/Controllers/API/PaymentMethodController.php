<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function dropdown()
    {
        $paymentMethods = PaymentMethodRepository::getPaymentMethodDropdown();
        return ResponseFormatter::success($paymentMethods, 'Metode Pembayaran berhasil diambil');
    }
}
