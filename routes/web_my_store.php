<?php

use App\Http\Controllers\MyStore\MyStoreController;
use App\Http\Controllers\MyStore\BrandController;
use App\Http\Controllers\MyStore\CategoryController;
use App\Http\Controllers\MyStore\CertificateController;
use App\Http\Controllers\MyStore\ColorController;
use App\Http\Controllers\MyStore\CustomerController;
use App\Http\Controllers\MyStore\MemberController;
use App\Http\Controllers\MyStore\MembershipController;
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
        Route::prefix('certificate')->name('certificate.')->group(function () {
            Route::get('/', [CertificateController::class, 'index'])->name('index');
            Route::get('/create', [CertificateController::class, 'create'])->name('create');
            Route::post('/', [CertificateController::class, 'store'])->name('store');
            Route::get('/{storeCertificate}', [CertificateController::class, 'edit'])->name('edit');
            Route::post('/{storeCertificate}', [CertificateController::class, 'update'])->name('update');
            Route::delete('/{storeCertificate}', [CertificateController::class, 'destroy'])->name('destroy');
        });

        // Category
        Route::prefix('category')->name('category.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}', [CategoryController::class, 'edit'])->name('edit');
            Route::post('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });

        // Color
        Route::prefix('color')->name('color.')->group(function () {
            Route::get('/', [ColorController::class, 'index'])->name('index');
            Route::get('/create', [ColorController::class, 'create'])->name('create');
            Route::post('/', [ColorController::class, 'store'])->name('store');
            Route::get('/{color}', [ColorController::class, 'edit'])->name('edit');
            Route::post('/{color}', [ColorController::class, 'update'])->name('update');
            Route::delete('/{color}', [ColorController::class, 'destroy'])->name('destroy');
        });

        // Size
        Route::prefix('size')->name('size.')->group(function () {
            Route::get('/', [SizeController::class, 'index'])->name('index');
            Route::get('/create', [SizeController::class, 'create'])->name('create');
            Route::post('/', [SizeController::class, 'store'])->name('store');
            Route::get('/{size}', [SizeController::class, 'edit'])->name('edit');
            Route::post('/{size}', [SizeController::class, 'update'])->name('update');
            Route::delete('/{size}', [SizeController::class, 'destroy'])->name('destroy');
        });

        // Brand
        Route::prefix('brand')->name('brand.')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('index');
            Route::get('/create', [BrandController::class, 'create'])->name('create');
            Route::post('/', [BrandController::class, 'store'])->name('store');
            Route::get('/{brand}', [BrandController::class, 'edit'])->name('edit');
            Route::post('/{brand}', [BrandController::class, 'update'])->name('update');
            Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');
        });

        // Unit
        Route::prefix('unit')->name('unit.')->group(function () {
            Route::get('/', [UnitController::class, 'index'])->name('index');
            Route::get('/create', [UnitController::class, 'create'])->name('create');
            Route::post('/', [UnitController::class, 'store'])->name('store');
            Route::get('/{unit}', [UnitController::class, 'edit'])->name('edit');
            Route::post('/{unit}', [UnitController::class, 'update'])->name('update');
            Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
        });

        // Product
        Route::prefix('product')->name('product.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'edit'])->name('edit');
            Route::post('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

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

        // Membership
        Route::prefix('membership')->name('membership.')->group(function () {
            Route::get('/', [MembershipController::class, 'index'])->name('index');
            Route::get('/create', [MembershipController::class, 'create'])->name('create');
            Route::post('/', [MembershipController::class, 'store'])->name('store');
            Route::get('/{membership}/edit', [MembershipController::class, 'edit'])->name('edit');
            Route::post('/{membership}', [MembershipController::class, 'update'])->name('update');
            Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('destroy');
        });

        // Member
        Route::prefix('member')->name('member.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::get('/{member}', [MemberController::class, 'show'])->name('show');
        });
    });
});
