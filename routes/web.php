<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductViewController;
use App\Http\Controllers\ClarksstoreController;

Route::get('/tryy', fn() => view('welcome'));

// 🛍️ مسارات Clarksstore (مستقلة)
Route::prefix('clarksstore')->group(function () {
    Route::get('/', [ClarksstoreController::class, 'index'])->name('clarksstore.index');
    Route::get('/categories/{id}/tree', [ClarksstoreController::class, 'getCategoryTree'])->name('clarksstore.tree');
});

// 🛒 مسارات الموقع العام (ProductView)
Route::get('/', [ProductViewController::class, 'index'])->name('categories.index');
Route::get('/categories/{id}/tree', [ProductViewController::class, 'getCategoryTree'])->name('categories.tree');
Route::get('/categories/{id}/products', [ProductViewController::class, 'getProductsByCategory'])->name('categories.products');
Route::get('/products/{id}', [ProductViewController::class, 'show'])->name('product.show');
