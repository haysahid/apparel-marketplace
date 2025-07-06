<?php

use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::post('/sync-cart', [OrderController::class, 'syncCart'])->name('sync-cart');

    // Deprecated routes, kept for reference
    Route::get('/provinces', [OrderController::class, 'provinces'])->name('provinces');

    // Deprecated code, kept for reference
    Route::get('/cities', [OrderController::class, 'cities'])->name('cities');

    Route::get('/destinations', [OrderController::class, 'destinations'])->name('destinations');

    Route::get('/shipping-cost', [OrderController::class, 'shippingCost'])->name('shipping-cost');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/cancel-order', [OrderController::class, 'cancelOrder'])->name('cancel-order');

        Route::get('/check-payment', [OrderController::class, 'checkPayment'])->name('check-payment');
        Route::put('/change-payment-type', [OrderController::class, 'changePaymentType'])->name('change-payment-type');
        Route::post('/confirm-payment', [OrderController::class, 'confirmPayment'])->name('confirm-payment');
    });
});

Route::name('api.admin.')->prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('product-image', ProductImageController::class);
});
