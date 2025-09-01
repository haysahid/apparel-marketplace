<?php

use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductImageController;
use App\Http\Controllers\API\ProductVariantController;
use App\Http\Controllers\API\ProductVariantImageController;
use App\Http\Controllers\API\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::post('/sync-cart', [OrderController::class, 'syncCart'])->name('sync-cart');

    // Deprecated routes, kept for reference
    Route::get('/provinces', [OrderController::class, 'provinces'])->name('provinces');

    // Deprecated routes, kept for reference
    Route::get('/cities', [OrderController::class, 'cities'])->name('cities');

    Route::get('/destinations', [OrderController::class, 'destinations'])->name('destinations');
    Route::get('/shipping-cost', [OrderController::class, 'shippingCost'])->name('shipping-cost');
    Route::post('/checkout-guest', [OrderController::class, 'checkoutGuest'])->name('checkout-guest');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/vouchers', [OrderController::class, 'getVouchers'])->name('vouchers');
        Route::post('/check-voucher', [OrderController::class, 'checkVoucher'])->name('check-voucher');

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

Route::name('api.my-store')->prefix('my-store')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('product', ProductController::class);
    Route::apiResource('product-image', ProductImageController::class);
    Route::apiResource('product-variant', ProductVariantController::class);
    Route::apiResource('product-variant-image', ProductVariantImageController::class);
    Route::put('change-order-status', [OrderController::class, 'changeStatus'])->name('order.change-status');
    Route::post('report/generate', [ReportController::class, 'generateReport'])->name('report.generate');
});
