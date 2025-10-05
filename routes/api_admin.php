<?php

use App\Http\Controllers\Admin\API\AdminStoreController;
use App\Http\Controllers\API\ProductImageController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.admin.')->prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('product-image', ProductImageController::class);

    // User
    Route::apiResource('user', UserController::class);
    Route::get('user/{userId}/point-transaction', [UserController::class, 'getUserPointTransactions'])->name('user.point-transaction');
    Route::get('user/{userId}/voucher', [UserController::class, 'getUserVouchers'])->name('user.voucher');

    // Store
    Route::get('store/{storeId}/invoice', [AdminStoreController::class, 'getStoreInvoices'])->name('store.invoice');
    Route::post('store/{storeId}/user-role', [AdminStoreController::class, 'addUserRole'])->name('store.user-role.add');
    Route::put('store/{storeId}/user-role/{userId}', [AdminStoreController::class, 'updateUserRole'])->name('store.user-role.update');
    Route::delete('store/{storeId}/user-role/{userId}', [AdminStoreController::class, 'removeUserRole'])->name('store.user-role.remove');
});
