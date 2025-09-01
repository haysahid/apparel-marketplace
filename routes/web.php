<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MyStore\MyStoreCertificateController;
use App\Http\Controllers\MyStoreController;
use App\Http\Controllers\MyStore\MyStoreProductController;
use App\Http\Controllers\MyStore\MyStoreBrandController;
use App\Http\Controllers\MyStore\MyStoreCategoryController;
use App\Http\Controllers\MyStore\MyStoreColorController;
use App\Http\Controllers\MyStore\MyStoreOrderController;
use App\Http\Controllers\MyStore\MyStoreSizeController;
use App\Http\Controllers\MyStore\MyStoreTransactionController;
use App\Http\Controllers\MyStore\MyStoreVoucherController;
use App\Http\Controllers\MyStore\MyStoreReportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StoreCertificateController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\StoreCertificate;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PublicController::class, 'home'])->name('home');

Route::get('/catalog', [PublicController::class, 'catalog'])->name('catalog');
Route::get('/product/{slug}', [PublicController::class, 'productDetail'])->name('product.show');
Route::get('/my-cart', [PublicController::class, 'myCart'])->name('my-cart');

Route::get('/login', [UserController::class, 'login'])->name('login');
Route::post('/login', [UserController::class, 'loginProcess'])->name('login.process');
Route::get('/register', [UserController::class, 'register'])->name('register');
Route::post('/register', [UserController::class, 'registerProcess'])->name('register.process');
Route::get('/profile', [UserController::class, 'profile'])->name('profile');
Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

Route::get('/checkout-guest', [OrderController::class, 'checkoutGuest'])->name('checkout.guest');
Route::get('/order-success-guest', [OrderController::class, 'orderSuccessGuest'])->name('order.success.guest');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/order-success', [OrderController::class, 'orderSuccess'])->name('order.success');
    Route::get('/my-order', [OrderController::class, 'myOrder'])->name('my-order');
    Route::get('/my-order/{invoice_code}', [OrderController::class, 'myOrderDetail'])->name('my-order.detail');

    Route::get('/create-store', [StoreController::class, 'create'])->name('store.create');
    Route::post('/create-store', [StoreController::class, 'store'])->name('store.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('login');
    Route::post('/login', [AdminController::class, 'loginProcess']);
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Store
        Route::get('/store-info', [StoreController::class, 'edit'])->name('store.edit');
        Route::post('/store-info', [StoreController::class, 'update'])->name('store.update');

        // Certificate
        Route::get('/certificate', [StoreCertificateController::class, 'index'])->name('certificate');
        Route::get('/certificate/create', [StoreCertificateController::class, 'create'])->name('certificate.create');
        Route::post('/certificate', [StoreCertificateController::class, 'store'])->name('certificate.store');
        Route::get('/certificate/{storeCertificate}', [StoreCertificateController::class, 'edit'])->name('certificate.edit');
        Route::post('/certificate/{storeCertificate}', [StoreCertificateController::class, 'update'])->name('certificate.update');
        Route::delete('/certificate/{storeCertificate}', [StoreCertificateController::class, 'destroy'])->name('certificate.destroy');

        // Product
        Route::get('/product', [ProductController::class, 'index'])->name('product');
        Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/product', [ProductController::class, 'store'])->name('product.store');
        Route::get('/product/{product}', [ProductController::class, 'edit'])->name('product.edit');
        Route::post('/product/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
    });
});

Route::prefix('my-store')->name('my-store.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [MyStoreController::class, 'index'])->name('index');
    Route::get('/select-store/{storeId}', [MyStoreController::class, 'selectStore'])->name('select-store');
    Route::get('/dashboard', [MyStoreController::class, 'dashboard'])->name('dashboard');

    // Store
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

    // Product
    Route::get('/products', [MyStoreProductController::class, 'index'])->name('product');
    Route::get('/products/create', [MyStoreProductController::class, 'create'])->name('product.create');
    Route::post('/products', [MyStoreProductController::class, 'store'])->name('product.store');
    Route::get('/products/{product}', [MyStoreProductController::class, 'edit'])->name('product.edit');
    Route::post('/products/{product}', [MyStoreProductController::class, 'update'])->name('product.update');
    Route::delete('/products/{product}', [MyStoreProductController::class, 'destroy'])->name('product.destroy');

    // Transaction
    Route::get('/transaction', [MyStoreTransactionController::class, 'index'])->name('transaction');
    Route::get('/transaction/{transaction}', [MyStoreTransactionController::class, 'edit'])->name('transaction.edit');

    // Order
    Route::get('/order', [MyStoreOrderController::class, 'index'])->name('order');
    Route::get('/order/{invoice}', [MyStoreOrderController::class, 'edit'])->name('order.edit');

    // Voucher
    Route::get('/voucher', [MyStoreVoucherController::class, 'index'])->name('voucher');
    Route::get('/voucher/create', [MyStoreVoucherController::class, 'create'])->name('voucher.create');
    Route::post('/voucher', [MyStoreVoucherController::class, 'store'])->name('voucher.store');
    Route::get('/voucher/{voucher}', [MyStoreVoucherController::class, 'edit'])->name('voucher.edit');
    Route::post('/voucher/{voucher}', [MyStoreVoucherController::class, 'update'])->name('voucher.update');
    Route::delete('/voucher/{voucher}', [MyStoreVoucherController::class, 'destroy'])->name('voucher.destroy');

    // Report
    Route::get('/report', [MyStoreReportController::class, 'index'])->name('report');
    Route::get('/report/preview', [MyStoreReportController::class, 'reportPreview'])->name('report.preview');
});

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });
