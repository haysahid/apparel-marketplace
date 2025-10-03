<?php

use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductImageController;
use App\Http\Controllers\API\ProductVariantController;
use App\Http\Controllers\API\ProductVariantImageController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\ShippingMethodController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VoucherController;
use App\Http\Controllers\InvoiceController;
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
    Route::get('/check-payment-guest', [OrderController::class, 'checkPayment'])->name('check-payment-guest');

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

// Admin Routes
Route::name('api.admin.')->prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('product-image', ProductImageController::class);
    Route::apiResource('user', UserController::class);
    Route::get('user/{userId}/point-transaction', [UserController::class, 'getUserPointTransactions'])->name('user.point-transaction');
    Route::get('user/{userId}/voucher', [UserController::class, 'getUserVouchers'])->name('user.voucher');
});

// My Store Routes
Route::name('api.my-store')->prefix('my-store')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('brand', BrandController::class);
    Route::get('brand-dropdown', [BrandController::class, 'dropdown'])->name('brand.dropdown');

    Route::apiResource('customer', CustomerController::class);
    Route::get('customer/{userId}/voucher', [CustomerController::class, 'getUserVouchers'])->name('customer.voucher');

    Route::apiResource('product', ProductController::class);
    Route::apiResource('product-image', ProductImageController::class);
    Route::apiResource('product-variant', ProductVariantController::class);
    Route::apiResource('product-variant-image', ProductVariantImageController::class);

    Route::post('checkout', [OrderController::class, 'checkoutStore'])->name('checkout');

    Route::get('payment-method-dropdown', [PaymentMethodController::class, 'dropdown'])->name('payment-method.dropdown');
    Route::get('shipping-method-dropdown', [ShippingMethodController::class, 'dropdown'])->name('shipping-method.dropdown');

    Route::get('midtrans-payment-methods', [OrderController::class, 'midtransPaymentMethods'])->name('midtrans.payment-methods');

    Route::put('change-order-status', [OrderController::class, 'changeStatus'])->name('order.change-status');

    Route::apiResource('invoice', InvoiceController::class);

    Route::apiResource('voucher', VoucherController::class);
    Route::get('voucher-dropdown', [VoucherController::class, 'dropdown'])->name('voucher.dropdown');

    Route::post('report/generate', [ReportController::class, 'generateReport'])->name('report.generate');
});
