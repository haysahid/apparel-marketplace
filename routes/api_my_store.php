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
use App\Http\Controllers\MyStore\API\MediaController;
use App\Http\Controllers\MyStore\API\MyStoreController;
use App\Http\Controllers\MyStore\API\ShipmentController;
use App\Http\Controllers\MyStore\API\UserController;
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

    Route::put('change-order-status', [\App\Http\Controllers\MyStore\API\OrderController::class, 'changeStatus'])->name('order.change-status');
    Route::put('set-order-delivering', [\App\Http\Controllers\MyStore\API\OrderController::class, 'setDelivering'])->name('order.set-delivering');

    // Invoice
    Route::apiResource('invoice', InvoiceController::class);

    // Shipment
    Route::apiResource('shipment', ShipmentController::class);
    Route::get('shipment-dropdown', [ShipmentController::class, 'dropdown'])->name('shipment.dropdown');

    // Voucher
    Route::apiResource('voucher', VoucherController::class);
    Route::get('voucher-dropdown', [VoucherController::class, 'dropdown'])->name('voucher.dropdown');

    // Report
    Route::post('report/generate', [ReportController::class, 'generateReport'])->name('report.generate');

    // Store Logo
    Route::post('store/logo', [MyStoreController::class, 'addStoreLogo'])->name('store-logo.add');
    Route::put('store/logo', [MyStoreController::class, 'updateStoreLogo'])->name('store-logo.update');
    Route::delete('store/logo', [MyStoreController::class, 'deleteStoreLogo'])->name('store-logo.delete');

    // Store Banner
    Route::post('store/banner', [MyStoreController::class, 'addStoreBanner'])->name('store-banner.add');
    Route::put('store/banner', [MyStoreController::class, 'updateStoreBanner'])->name('store-banner.update');
    Route::delete('store/banner', [MyStoreController::class, 'deleteStoreBanner'])->name('store-banner.delete');

    // Store User Role
    Route::post('user/{userId}/role', [MyStoreController::class, 'addUserRole'])->name('user-role.add');
    Route::put('user/{userId}/role', [MyStoreController::class, 'updateUserRole'])->name('user-role.update');
    Route::delete('user/{userId}/role', [MyStoreController::class, 'removeUserRole'])->name('user-role.remove');

    // User
    Route::get('user', [UserController::class, 'index'])->name('user.index');
    Route::get('user-dropdown', [UserController::class, 'dropdown'])->name('user.dropdown');

    // Media
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::get('/{id}', [MediaController::class, 'show'])->name('show');
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
    });

    // Temporary Media
    Route::get('temporary-media', [MediaController::class, 'getAllTemporaryMedia'])->name('temporary-media.index');
    Route::post('temporary-media/upload', [MediaController::class, 'uploadTemporaryMedia'])->name('temporary-media.upload');
});
