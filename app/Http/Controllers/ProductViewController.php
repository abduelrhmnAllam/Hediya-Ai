<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class ProductViewController extends Controller
{
    public function index()
    {
             // الفئات الرئيسية فقط (اللي مفيش parent_id ليها)
        $categories = Category::whereNull('parent_id')
            ->with('children.children') // تحميل الفروع الفرعية كمان
            ->get();

        return view('products-demo', compact('categories'));
    }

        public function getCategoryTree($id)
    {
        // الفئة المطلوبة مع كل الفروع التابعة ليها + منتجاتها
        $category = Category::with(['children.children', 'products'])->findOrFail($id);

        return response()->json([
            'category' => $category
        ]);
    }
    
    public function getProductsByCategory($id)
    {
        // جلب المنتجات الخاصة بفئة معينة
        $products = Product::where('category_id', $id)->get();

        return response()->json(['products' => $products]);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return view('product-details', compact('product'));
    }
}
