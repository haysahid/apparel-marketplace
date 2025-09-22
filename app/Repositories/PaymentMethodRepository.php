<?php

namespace App\Repositories;

use App\Models\PaymentMethod;

class PaymentMethodRepository
{
    public static function getPaymentMethodDropdown(
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $paymentMethods = PaymentMethod::query();
        $paymentMethods->orderBy($orderBy, $orderDirection);
        return $paymentMethods->get();
    }
}
