<?php

namespace App\Http\Controllers;

use App\Models\ClarksstoreCategory;
use App\Models\ClarksstoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ClarksstoreController extends Controller
{
    public function index()
    {
        // نجيب كل الفئات
        $categories = ClarksstoreCategory::orderBy('name')->get();
        return view('clarksstore.index', compact('categories'));
    }

    public function getCategoryTree($id)
{
    \Log::info("➡️ getCategoryTree CALLED with ID = {$id}");

    $category = \App\Models\ClarksstoreCategory::find($id);

    if (!$category) {
        \Log::warning("⚠️ Category {$id} not found in DB");
        return response()->json(['error' => 'Category not found'], 404);
    }

    \Log::info("✅ Category found: {$category->name} (ID: {$category->id})");

    $products = \App\Models\ClarksstoreProduct::where('category_id', $category->id)
        ->select('id', 'name', 'price', 'currency', 'url', 'picture', 'offer_id')
        ->limit(20)
        ->get()
        ->map(function ($p) {
            $pictures = [];

         $pictures = $p->picture ? [trim($p->picture)] : [];


            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'currency_id' => $p->currency,
                'url' => $p->url,
                'pictures' => $pictures,
            ];
        });

    // ✅ لازم نرجع JSON هنا
    return response()->json([
        'category' => [
            'id' => $category->id,
            'name' => $category->name,
            'products' => $products,
            'children' => [],
        ]
    ]);
}


}
