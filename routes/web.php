<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
Route::get('/my-order-guest/{invoice_code}', [OrderController::class, 'myOrderDetail'])->name('my-order.detail.guest');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/order-success', [OrderController::class, 'orderSuccess'])->name('order.success');
    Route::get('/my-order', [OrderController::class, 'myOrder'])->name('my-order');
    Route::get('/my-order/{invoice_code}', [OrderController::class, 'myOrderDetail'])->name('my-order.detail');

    Route::get('/create-store', [StoreController::class, 'create'])->name('store.create');
    Route::post('/create-store', [StoreController::class, 'store'])->name('store.store');
});

require __DIR__ . '/web_admin.php';
require __DIR__ . '/web_my_store.php';

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });
