<?php

namespace App\Repositories;

use App\Models\ShippingMethod;

class ShippingMethodRepository
{
    public static function getShippingMethodDropdown(
        $orderBy = 'created_at',
        $orderDirection = 'desc'
    ) {
        $paymentMethods = ShippingMethod::query();
        $paymentMethods->orderBy($orderBy, $orderDirection);
        return $paymentMethods->get();
    }
}
