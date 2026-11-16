<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product:slug}/order', [ProductController::class, 'order'])
    ->middleware('throttle:60,1')
    ->name('products.order');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/cart/sync', [CartController::class, 'sync'])->name('cart.sync');
    Route::post('/cart/order', [CartController::class, 'order'])->name('cart.order');
});

Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'create'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'store'])->middleware('throttle:login');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');

        Route::redirect('/', '/admin/products')->name('dashboard');

        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::patch('/products/{product}/status', [Admin\ProductStatusController::class, 'update'])->name('products.status');

        Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');

        Route::get('/password', [Admin\PasswordController::class, 'edit'])->name('password.edit');
        Route::put('/password', [Admin\PasswordController::class, 'update'])->name('password.update');
    });
});
