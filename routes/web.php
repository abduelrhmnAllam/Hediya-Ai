<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductViewController;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/products-demo', [ProductViewController::class, 'index'])->name('categories.index');

Route::get('/categories/{id}/tree', [ProductViewController::class, 'getCategoryTree'])->name('categories.tree');
Route::get('/categories/{id}/products', [ProductViewController::class, 'getProductsByCategory'])->name('categories.products');
Route::get('/products/{id}', [ProductViewController::class, 'show'])->name('product.show');
