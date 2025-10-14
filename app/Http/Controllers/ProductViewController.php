<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductViewController extends Controller
{
    public function index()
    {
        // نجلب المنتجات مع الفئة المرتبطة
        $products = Product::with('category')
            ->orderBy('id', 'desc')
            ->take(20) // نعرض 20 منتج مؤقتًا
            ->get();

        return view('products-demo', compact('products'));
    }

    public function show($id)
{
    $product = \App\Models\Product::with('category')->findOrFail($id);

    return view('product-details', compact('product'));
}

}
