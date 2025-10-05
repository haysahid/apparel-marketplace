<?php

use App\Http\Controllers\Admin\AdminStoreController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('login');
    Route::post('/login', [AdminController::class, 'loginProcess']);
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // User
        Route::get('/user', [AdminUserController::class, 'index'])->name('user');
        Route::get('/user/create', [AdminUserController::class, 'create'])->name('user.create');
        Route::get('/user/{user}', [AdminUserController::class, 'show'])->name('user.show');
        Route::post('/user', [AdminUserController::class, 'store'])->name('user.store');
        Route::get('/user/{user}/edit', [AdminUserController::class, 'edit'])->name('user.edit');
        Route::post('/user/{user}', [AdminUserController::class, 'update'])->name('user.update');
        Route::delete('/user/{user}', [AdminUserController::class, 'destroy'])->name('user.destroy');

        // Store
        Route::get('/store', [AdminStoreController::class, 'index'])->name('store');
        Route::get('/store/create', [AdminStoreController::class, 'create'])->name('store.create');
        Route::get('/store/{store}', [AdminStoreController::class, 'show'])->name('store.show');
        Route::post('/store', [AdminStoreController::class, 'store'])->name('store.store');
        Route::get('/store/{store}/edit', [AdminStoreController::class, 'edit'])->name('store.edit');
        Route::post('/store/{store}', [AdminStoreController::class, 'update'])->name('store.update');
        Route::delete('/store/{store}', [AdminStoreController::class, 'destroy'])->name('store.destroy');
    });
});
