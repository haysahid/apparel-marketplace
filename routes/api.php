<?php

use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// General Routes
Route::name('api.')->group(function () {
    Route::post('/sync-cart', [OrderController::class, 'syncCart'])->name('sync-cart');

    // Deprecated routes, kept for reference
    Route::get('/provinces', [OrderController::class, 'provinces'])->name('provinces');

    // Deprecated routes, kept for reference
    Route::get('/cities', [OrderController::class, 'cities'])->name('cities');

    Route::get('/destinations', [OrderController::class, 'destinations'])->name('destinations');
    Route::get('/shipping-cost', [OrderController::class, 'shippingCost'])->name('shipping-cost');

    Route::post('/checkout-guest', [OrderController::class, 'checkoutGuest'])->name('checkout-guest');
    Route::get('/check-payment-guest', [OrderController::class, 'checkPayment'])->name('check-payment-guest');
    Route::get('/shipment-dropdown-guest', [ShipmentController::class, 'dropdown'])->name('shipment.dropdown');
    Route::put('/change-payment-type-guest', [OrderController::class, 'changePaymentType'])->name('change-payment-type-guest');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/voucher', [OrderController::class, 'getVouchers'])->name('voucher');
        Route::post('/check-voucher', [OrderController::class, 'checkVoucher'])->name('check-voucher');

        Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/cancel-order', [OrderController::class, 'cancelOrder'])->name('cancel-order');

        Route::get('/check-payment', [OrderController::class, 'checkPayment'])->name('check-payment');
        Route::put('/change-payment-type', [OrderController::class, 'changePaymentType'])->name('change-payment-type');
        Route::post('/confirm-payment', [OrderController::class, 'confirmPayment'])->name('confirm-payment');
    });
});

require __DIR__ . '/api_admin.php';
require __DIR__ . '/api_my_store.php';
