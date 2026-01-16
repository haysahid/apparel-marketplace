<?php

use App\Http\Controllers\MyStore\MyStoreController;
use App\Http\Controllers\MyStore\BrandController;
use App\Http\Controllers\MyStore\CategoryController;
use App\Http\Controllers\MyStore\CertificateController;
use App\Http\Controllers\MyStore\ColorController;
use App\Http\Controllers\MyStore\CustomerController;
use App\Http\Controllers\MyStore\MemberController;
use App\Http\Controllers\MyStore\MembershipTypeController;
use App\Http\Controllers\MyStore\OrderController;
use App\Http\Controllers\MyStore\PartnerController;
use App\Http\Controllers\MyStore\PaymentController;
use App\Http\Controllers\MyStore\PointRuleController;
use App\Http\Controllers\MyStore\ProductController;
use App\Http\Controllers\MyStore\ReportController;
use App\Http\Controllers\MyStore\SizeController;
use App\Http\Controllers\MyStore\TransactionController;
use App\Http\Controllers\MyStore\UnitController;
use App\Http\Controllers\MyStore\VoucherController;
use App\Http\Middleware\MyStoreMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('my-store')->name('my-store.')->middleware(['auth'])->group(function () {
    Route::get('/select-store/{storeId}', [MyStoreController::class, 'selectStore'])->name('select-store');

    Route::middleware([MyStoreMiddleware::class])->group(function () {
        Route::get('/', [MyStoreController::class, 'index'])->name('index');
        Route::get('/dashboard', [MyStoreController::class, 'dashboard'])->name('dashboard');

        // Store
        Route::get('/store', [MyStoreController::class, 'show'])->name('store.show');
        Route::get('/store-info', [MyStoreController::class, 'storeInfo'])->name('store.edit');
        Route::post('/store-info', [MyStoreController::class, 'updateStore'])->name('store.update');

        // Certificate
        Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate');
        Route::get('/certificate/create', [CertificateController::class, 'create'])->name('certificate.create');
        Route::post('/certificate', [CertificateController::class, 'store'])->name('certificate.store');
        Route::get('/certificate/{storeCertificate}', [CertificateController::class, 'edit'])->name('certificate.edit');
        Route::post('/certificate/{storeCertificate}', [CertificateController::class, 'update'])->name('certificate.update');
        Route::delete('/certificate/{storeCertificate}', [CertificateController::class, 'destroy'])->name('certificate.destroy');

        // Category
        Route::get('/category', [CategoryController::class, 'index'])->name('category');
        Route::get('/category/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/category', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/category/{category}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/category/{category}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');

        // Color
        Route::get('/color', [ColorController::class, 'index'])->name('color');
        Route::get('/color/create', [ColorController::class, 'create'])->name('color.create');
        Route::post('/color', [ColorController::class, 'store'])->name('color.store');
        Route::get('/color/{color}', [ColorController::class, 'edit'])->name('color.edit');
        Route::post('/color/{color}', [ColorController::class, 'update'])->name('color.update');
        Route::delete('/color/{color}', [ColorController::class, 'destroy'])->name('color.destroy');

        // Size
        Route::get('/size', [SizeController::class, 'index'])->name('size');
        Route::get('/size/create', [SizeController::class, 'create'])->name('size.create');
        Route::post('/size', [SizeController::class, 'store'])->name('size.store');
        Route::get('/size/{size}', [SizeController::class, 'edit'])->name('size.edit');
        Route::post('/size/{size}', [SizeController::class, 'update'])->name('size.update');
        Route::delete('/size/{size}', [SizeController::class, 'destroy'])->name('size.destroy');

        // Brand
        Route::get('/brand', [BrandController::class, 'index'])->name('brand');
        Route::get('/brand/create', [BrandController::class, 'create'])->name('brand.create');
        Route::post('/brand', [BrandController::class, 'store'])->name('brand.store');
        Route::get('/brand/{brand}', [BrandController::class, 'edit'])->name('brand.edit');
        Route::post('/brand/{brand}', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('/brand/{brand}', [BrandController::class, 'destroy'])->name('brand.destroy');

        // Unit
        Route::get('/unit', [UnitController::class, 'index'])->name('unit');
        Route::get('/unit/create', [UnitController::class, 'create'])->name('unit.create');
        Route::post('/unit', [UnitController::class, 'store'])->name('unit.store');
        Route::get('/unit/{unit}', [UnitController::class, 'edit'])->name('unit.edit');
        Route::post('/unit/{unit}', [UnitController::class, 'update'])->name('unit.update');
        Route::delete('/unit/{unit}', [UnitController::class, 'destroy'])->name('unit.destroy');

        // Product
        Route::get('/product', [ProductController::class, 'index'])->name('product');
        Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/product', [ProductController::class, 'store'])->name('product.store');
        Route::get('/product/{product}', [ProductController::class, 'edit'])->name('product.edit');
        Route::post('/product/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');

        // Transaction
        Route::get('/transaction', [TransactionController::class, 'index'])->name('transaction');
        Route::get('/transaction/{transaction}', [TransactionController::class, 'edit'])->name('transaction.edit');

        // Order
        Route::get('/order', [OrderController::class, 'index'])->name('order');
        Route::get('/order/create', [OrderController::class, 'create'])->name('order.create');
        Route::get('/order/{invoice}', [OrderController::class, 'edit'])->name('order.edit');

        // Payment
        Route::get('/payment', [PaymentController::class, 'index'])->name('payment');

        // Voucher
        Route::get('/voucher', [VoucherController::class, 'index'])->name('voucher');
        Route::get('/voucher/create', [VoucherController::class, 'create'])->name('voucher.create');
        Route::post('/voucher', [VoucherController::class, 'store'])->name('voucher.store');
        Route::get('/voucher/{voucher}/edit', [VoucherController::class, 'edit'])->name('voucher.edit');
        Route::post('/voucher/{voucher}', [VoucherController::class, 'update'])->name('voucher.update');
        Route::delete('/voucher/{voucher}', [VoucherController::class, 'destroy'])->name('voucher.destroy');

        // Customer
        Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
        Route::get('/customer/{customer}', [CustomerController::class, 'show'])->name('customer.show');

        // Point Rule
        Route::get('/point-rule', [PointRuleController::class, 'index'])->name('point-rule');
        Route::get('/point-rule/create', [PointRuleController::class, 'create'])->name('point-rule.create');
        Route::post('/point-rule', [PointRuleController::class, 'store'])->name('point-rule.store');
        Route::get('/point-rule/{pointRule}', [PointRuleController::class, 'edit'])->name('point-rule.edit');
        Route::post('/point-rule/{pointRule}', [PointRuleController::class, 'update'])->name('point-rule.update');
        Route::delete('/point-rule/{pointRule}', [PointRuleController::class, 'destroy'])->name('point-rule.destroy');

        // Partner
        Route::get('/partner', [PartnerController::class, 'index'])->name('partner');
        Route::get('/partner/{partner}', [PartnerController::class, 'show'])->name('partner.show');
        Route::get('/partner/create', [PartnerController::class, 'create'])->name('partner.create');
        Route::post('/partner', [PartnerController::class, 'store'])->name('partner.store');
        Route::get('/partner/{partner}/edit', [PartnerController::class, 'edit'])->name('partner.edit');
        Route::post('/partner/{partner}', [PartnerController::class, 'update'])->name('partner.update');
        Route::delete('/partner/{partner}', [PartnerController::class, 'destroy'])->name('partner.destroy');

        // Report
        Route::get('/report', [ReportController::class, 'index'])->name('report');
        Route::get('/report/preview', [ReportController::class, 'reportPreview'])->name('report.preview');

        // Membership Type
        Route::prefix('membership-type')->name('membership-type.')->group(function () {
            Route::get('/', [MembershipTypeController::class, 'index'])->name('index');
            Route::get('/create', [MembershipTypeController::class, 'create'])->name('create');
            Route::post('/', [MembershipTypeController::class, 'store'])->name('store');
            Route::get('/{membershipType}/edit', [MembershipTypeController::class, 'edit'])->name('edit');
            Route::post('/{membershipType}', [MembershipTypeController::class, 'update'])->name('update');
            Route::delete('/{membershipType}', [MembershipTypeController::class, 'destroy'])->name('destroy');
        });

        // Member
        Route::prefix('member')->name('member.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::get('/{member}', [MemberController::class, 'show'])->name('show');
        });
    });
});
