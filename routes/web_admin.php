<?php

use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\UserController;
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
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::post('/user/{user}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

        // Store
        Route::get('/store', [StoreController::class, 'index'])->name('store');
        Route::get('/store/create', [StoreController::class, 'create'])->name('store.create');
        Route::get('/store/{store}', [StoreController::class, 'show'])->name('store.show');
        Route::post('/store', [StoreController::class, 'store'])->name('store.store');
        Route::get('/store/{store}/edit', [StoreController::class, 'edit'])->name('store.edit');
        Route::post('/store/{store}', [StoreController::class, 'update'])->name('store.update');
        Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');
    });
});
