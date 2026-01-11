<?php

use App\Http\Controllers\Admin\API\AdminStoreController;
use App\Http\Controllers\API\ProductImageController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.admin.')->prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('product-image', ProductImageController::class);

    // User Routes
    Route::prefix('user')->name('user.')->group(function () {
        Route::apiResource('/', UserController::class)->parameters(['' => 'user']);
        Route::get('{userId}/point-transaction', [UserController::class, 'getUserPointTransactions'])->name('point-transaction');
        Route::get('{userId}/voucher', [UserController::class, 'getUserVouchers'])->name('voucher');
    });

    // Store Routes
    Route::prefix('store')->name('store.')->group(function () {
        // Invoice
        Route::get('{storeId}/invoice', [AdminStoreController::class, 'getStoreInvoices'])->name('invoice');

        // Store User Role
        Route::prefix('{storeId}/user-role')->name('user-role.')->group(function () {
            Route::post('/', [AdminStoreController::class, 'addUserRole'])->name('add');
            Route::put('{userId}', [AdminStoreController::class, 'updateUserRole'])->name('update');
            Route::delete('{userId}', [AdminStoreController::class, 'removeUserRole'])->name('remove');
        });

        // Store Logo
        Route::prefix('{storeId}/logo')->name('logo.')->group(function () {
            Route::post('/', [AdminStoreController::class, 'addStoreLogo'])->name('add');
            Route::put('/', [AdminStoreController::class, 'updateStoreLogo'])->name('update');
            Route::delete('/', [AdminStoreController::class, 'deleteStoreLogo'])->name('delete');
        });

        // Store Banner
        Route::prefix('{storeId}/banner')->name('banner.')->group(function () {
            Route::post('/', [AdminStoreController::class, 'addStoreBanner'])->name('add');
            Route::put('/', [AdminStoreController::class, 'updateStoreBanner'])->name('update');
            Route::delete('/', [AdminStoreController::class, 'deleteStoreBanner'])->name('delete');
        });
    });
});
