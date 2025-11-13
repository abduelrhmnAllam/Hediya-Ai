<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductViewController;
use App\Http\Controllers\ClarksstoreController;

use App\Http\Controllers\FeedController;
use App\Http\Controllers\FeedRunController;

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

Route::get('/feeds/products', [ProductViewController::class, 'index'])->name('feeds.products');



  Route::get('/feeds/import', [FeedController::class, 'importForm']);
Route::post('/feeds/import', [FeedController::class, 'importUpload']);
Route::post('/feeds/upload', [FeedController::class, 'uploadFile']);
Route::get('/feeds/runs', [FeedRunController::class, 'index']);
Route::get('/feeds/runs/{run}', [FeedRunController::class, 'show']);
Route::delete('/feeds/runs/{run}', [FeedRunController::class, 'destroy']);

