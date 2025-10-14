<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductViewController;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/products-demo', [ProductViewController::class, 'index']);
Route::get('/products-demo/{id}', [ProductViewController::class, 'show'])->name('product.show');
