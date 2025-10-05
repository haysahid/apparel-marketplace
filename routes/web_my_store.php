<?php

use App\Http\Controllers\MyStore\MyStoreBrandController;
use App\Http\Controllers\MyStore\MyStoreCategoryController;
use App\Http\Controllers\MyStore\MyStoreCertificateController;
use App\Http\Controllers\MyStore\MyStoreColorController;
use App\Http\Controllers\MyStore\MyStoreCustomerController;
use App\Http\Controllers\MyStore\MyStoreOrderController;
use App\Http\Controllers\MyStore\MyStorePartnerController;
use App\Http\Controllers\MyStore\MyStorePaymentController;
use App\Http\Controllers\MyStore\MyStorePointRuleController;
use App\Http\Controllers\MyStore\MyStoreProductController;
use App\Http\Controllers\MyStore\MyStoreReportController;
use App\Http\Controllers\MyStore\MyStoreSizeController;
use App\Http\Controllers\MyStore\MyStoreTransactionController;
use App\Http\Controllers\MyStore\MyStoreUnitController;
use App\Http\Controllers\MyStore\MyStoreVoucherController;
use App\Http\Controllers\MyStoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('my-store')->name('my-store.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [MyStoreController::class, 'index'])->name('index');
    Route::get('/select-store/{storeId}', [MyStoreController::class, 'selectStore'])->name('select-store');
    Route::get('/dashboard', [MyStoreController::class, 'dashboard'])->name('dashboard');

    // Store
    Route::get('/store', [MyStoreController::class, 'show'])->name('store.show');
    Route::get('/store-info', [MyStoreController::class, 'storeInfo'])->name('store.edit');
    Route::post('/store-info', [MyStoreController::class, 'updateStore'])->name('store.update');

    // Certificate
    Route::get('/certificate', [MyStoreCertificateController::class, 'index'])->name('certificate');
    Route::get('/certificate/create', [MyStoreCertificateController::class, 'create'])->name('certificate.create');
    Route::post('/certificate', [MyStoreCertificateController::class, 'store'])->name('certificate.store');
    Route::get('/certificate/{storeCertificate}', [MyStoreCertificateController::class, 'edit'])->name('certificate.edit');
    Route::post('/certificate/{storeCertificate}', [MyStoreCertificateController::class, 'update'])->name('certificate.update');
    Route::delete('/certificate/{storeCertificate}', [MyStoreCertificateController::class, 'destroy'])->name('certificate.destroy');

    // Category
    Route::get('/category', [MyStoreCategoryController::class, 'index'])->name('category');
    Route::get('/category/create', [MyStoreCategoryController::class, 'create'])->name('category.create');
    Route::post('/category', [MyStoreCategoryController::class, 'store'])->name('category.store');
    Route::get('/category/{category}', [MyStoreCategoryController::class, 'edit'])->name('category.edit');
    Route::post('/category/{category}', [MyStoreCategoryController::class, 'update'])->name('category.update');
    Route::delete('/category/{category}', [MyStoreCategoryController::class, 'destroy'])->name('category.destroy');

    // Color
    Route::get('/color', [MyStoreColorController::class, 'index'])->name('color');
    Route::get('/color/create', [MyStoreColorController::class, 'create'])->name('color.create');
    Route::post('/color', [MyStoreColorController::class, 'store'])->name('color.store');
    Route::get('/color/{color}', [MyStoreColorController::class, 'edit'])->name('color.edit');
    Route::post('/color/{color}', [MyStoreColorController::class, 'update'])->name('color.update');
    Route::delete('/color/{color}', [MyStoreColorController::class, 'destroy'])->name('color.destroy');

    // Size
    Route::get('/size', [MyStoreSizeController::class, 'index'])->name('size');
    Route::get('/size/create', [MyStoreSizeController::class, 'create'])->name('size.create');
    Route::post('/size', [MyStoreSizeController::class, 'store'])->name('size.store');
    Route::get('/size/{size}', [MyStoreSizeController::class, 'edit'])->name('size.edit');
    Route::post('/size/{size}', [MyStoreSizeController::class, 'update'])->name('size.update');
    Route::delete('/size/{size}', [MyStoreSizeController::class, 'destroy'])->name('size.destroy');

    // Brand
    Route::get('/brand', [MyStoreBrandController::class, 'index'])->name('brand');
    Route::get('/brand/create', [MyStoreBrandController::class, 'create'])->name('brand.create');
    Route::post('/brand', [MyStoreBrandController::class, 'store'])->name('brand.store');
    Route::get('/brand/{brand}', [MyStoreBrandController::class, 'edit'])->name('brand.edit');
    Route::post('/brand/{brand}', [MyStoreBrandController::class, 'update'])->name('brand.update');
    Route::delete('/brand/{brand}', [MyStoreBrandController::class, 'destroy'])->name('brand.destroy');

    // Unit
    Route::get('/unit', [MyStoreUnitController::class, 'index'])->name('unit');
    Route::get('/unit/create', [MyStoreUnitController::class, 'create'])->name('unit.create');
    Route::post('/unit', [MyStoreUnitController::class, 'store'])->name('unit.store');
    Route::get('/unit/{unit}', [MyStoreUnitController::class, 'edit'])->name('unit.edit');
    Route::post('/unit/{unit}', [MyStoreUnitController::class, 'update'])->name('unit.update');
    Route::delete('/unit/{unit}', [MyStoreUnitController::class, 'destroy'])->name('unit.destroy');

    // Product
    Route::get('/product', [MyStoreProductController::class, 'index'])->name('product');
    Route::get('/product/create', [MyStoreProductController::class, 'create'])->name('product.create');
    Route::post('/product', [MyStoreProductController::class, 'store'])->name('product.store');
    Route::get('/product/{product}', [MyStoreProductController::class, 'edit'])->name('product.edit');
    Route::post('/product/{product}', [MyStoreProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{product}', [MyStoreProductController::class, 'destroy'])->name('product.destroy');

    // Transaction
    Route::get('/transaction', [MyStoreTransactionController::class, 'index'])->name('transaction');
    Route::get('/transaction/{transaction}', [MyStoreTransactionController::class, 'edit'])->name('transaction.edit');

    // Order
    Route::get('/order', [MyStoreOrderController::class, 'index'])->name('order');
    Route::get('/order/create', [MyStoreOrderController::class, 'create'])->name('order.create');
    Route::get('/order/{invoice}', [MyStoreOrderController::class, 'edit'])->name('order.edit');

    // Payment
    Route::get('/payment', [MyStorePaymentController::class, 'index'])->name('payment');

    // Voucher
    Route::get('/voucher', [MyStoreVoucherController::class, 'index'])->name('voucher');
    Route::get('/voucher/create', [MyStoreVoucherController::class, 'create'])->name('voucher.create');
    Route::post('/voucher', [MyStoreVoucherController::class, 'store'])->name('voucher.store');
    Route::get('/voucher/{voucher}/edit', [MyStoreVoucherController::class, 'edit'])->name('voucher.edit');
    Route::post('/voucher/{voucher}', [MyStoreVoucherController::class, 'update'])->name('voucher.update');
    Route::delete('/voucher/{voucher}', [MyStoreVoucherController::class, 'destroy'])->name('voucher.destroy');

    // Customer
    Route::get('/customer', [MyStoreCustomerController::class, 'index'])->name('customer');
    Route::get('/customer/{customer}', [MyStoreCustomerController::class, 'show'])->name('customer.show');

    // Point Rule
    Route::get('/point-rule', [MyStorePointRuleController::class, 'index'])->name('point-rule');
    Route::get('/point-rule/create', [MyStorePointRuleController::class, 'create'])->name('point-rule.create');
    Route::post('/point-rule', [MyStorePointRuleController::class, 'store'])->name('point-rule.store');
    Route::get('/point-rule/{pointRule}', [MyStorePointRuleController::class, 'edit'])->name('point-rule.edit');
    Route::post('/point-rule/{pointRule}', [MyStorePointRuleController::class, 'update'])->name('point-rule.update');
    Route::delete('/point-rule/{pointRule}', [MyStorePointRuleController::class, 'destroy'])->name('point-rule.destroy');

    // Partner
    Route::get('/partner', [MyStorePartnerController::class, 'index'])->name('partner');
    Route::get('/partner/{partner}', [MyStorePartnerController::class, 'show'])->name('partner.show');
    Route::get('/partner/create', [MyStorePartnerController::class, 'create'])->name('partner.create');
    Route::post('/partner', [MyStorePartnerController::class, 'store'])->name('partner.store');
    Route::get('/partner/{partner}/edit', [MyStorePartnerController::class, 'edit'])->name('partner.edit');
    Route::post('/partner/{partner}', [MyStorePartnerController::class, 'update'])->name('partner.update');
    Route::delete('/partner/{partner}', [MyStorePartnerController::class, 'destroy'])->name('partner.destroy');

    // Report
    Route::get('/report', [MyStoreReportController::class, 'index'])->name('report');
    Route::get('/report/preview', [MyStoreReportController::class, 'reportPreview'])->name('report.preview');
});
