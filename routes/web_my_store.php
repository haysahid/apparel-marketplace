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
        Route::prefix('transaction')->name('transaction.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [TransactionController::class, 'edit'])->name('edit');
        });

        // Order
        Route::prefix('order')->name('order.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::get('/{invoice}', [OrderController::class, 'edit'])->name('edit');
        });

        // Payment
        Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');

        // Voucher
        Route::prefix('voucher')->name('voucher.')->group(function () {
            Route::get('/', [VoucherController::class, 'index'])->name('index');
            Route::get('/create', [VoucherController::class, 'create'])->name('create');
            Route::post('/', [VoucherController::class, 'store'])->name('store');
            Route::get('/{voucher}/edit', [VoucherController::class, 'edit'])->name('edit');
            Route::post('/{voucher}', [VoucherController::class, 'update'])->name('update');
            Route::delete('/{voucher}', [VoucherController::class, 'destroy'])->name('destroy');
        });

        // Customer
        Route::prefix('customer')->name('customer.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        });

        // Point Rule
        Route::prefix('point-rule')->name('point-rule.')->group(function () {
            Route::get('/', [PointRuleController::class, 'index'])->name('index');
            Route::get('/create', [PointRuleController::class, 'create'])->name('create');
            Route::post('/', [PointRuleController::class, 'store'])->name('store');
            Route::get('/{pointRule}', [PointRuleController::class, 'edit'])->name('edit');
            Route::post('/{pointRule}', [PointRuleController::class, 'update'])->name('update');
            Route::delete('/{pointRule}', [PointRuleController::class, 'destroy'])->name('destroy');
        });

        // Partner
        Route::prefix('partner')->name('partner.')->group(function () {
            Route::get('/', [PartnerController::class, 'index'])->name('index');
            Route::get('/create', [PartnerController::class, 'create'])->name('create');
            Route::post('/', [PartnerController::class, 'store'])->name('store');
            Route::get('/{partner}', [PartnerController::class, 'show'])->name('show');
            Route::get('/{partner}/edit', [PartnerController::class, 'edit'])->name('edit');
            Route::post('/{partner}', [PartnerController::class, 'update'])->name('update');
            Route::delete('/{partner}', [PartnerController::class, 'destroy'])->name('destroy');
        });

        // Report
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
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
