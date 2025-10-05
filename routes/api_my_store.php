<?php

use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductImageController;
use App\Http\Controllers\API\ProductVariantController;
use App\Http\Controllers\API\ProductVariantImageController;
use App\Http\Controllers\API\ShippingMethodController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\VoucherController;
use App\Http\Controllers\MyStore\API\MyStoreController;
use Illuminate\Support\Facades\Route;

Route::name('api.my-store')->prefix('my-store')->middleware('auth:sanctum')->group(function () {
    // Brand
    Route::apiResource('brand', BrandController::class);
    Route::get('brand-dropdown', [BrandController::class, 'dropdown'])->name('brand.dropdown');

    // Customer
    Route::apiResource('customer', CustomerController::class);
    Route::get('customer/{userId}/voucher', [CustomerController::class, 'getUserVouchers'])->name('customer.voucher');

    // Product
    Route::apiResource('product', ProductController::class);
    Route::apiResource('product-image', ProductImageController::class);
    Route::apiResource('product-variant', ProductVariantController::class);
    Route::apiResource('product-variant-image', ProductVariantImageController::class);

    // Order
    Route::post('checkout', [OrderController::class, 'checkoutStore'])->name('checkout');

    Route::get('payment-method-dropdown', [PaymentMethodController::class, 'dropdown'])->name('payment-method.dropdown');
    Route::get('shipping-method-dropdown', [ShippingMethodController::class, 'dropdown'])->name('shipping-method.dropdown');

    Route::get('midtrans-payment-methods', [OrderController::class, 'midtransPaymentMethods'])->name('midtrans.payment-methods');

    Route::put('change-order-status', [OrderController::class, 'changeStatus'])->name('order.change-status');

    Route::apiResource('invoice', InvoiceController::class);

    Route::apiResource('voucher', VoucherController::class);
    Route::get('voucher-dropdown', [VoucherController::class, 'dropdown'])->name('voucher.dropdown');

    Route::post('report/generate', [ReportController::class, 'generateReport'])->name('report.generate');

    // User Role
    Route::post('user/{userId}/role', [MyStoreController::class, 'addUserRole'])->name('user-role.add');
    Route::put('user/{userId}/role', [MyStoreController::class, 'updateUserRole'])->name('user-role.update');
    Route::delete('user/{userId}/role', [MyStoreController::class, 'removeUserRole'])->name('user-role.remove');
});
