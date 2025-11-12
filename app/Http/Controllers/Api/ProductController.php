<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->whereHas('feedItems') // لازم يكون له سعر
            ->with(['brand','images','colors','sizes','categories','feedItems']);

        // future filters placeholder
        if ($request->brand) {
            $query->whereHas('brand', fn($q)=>$q->where('slug', $request->brand));
        }

        if ($request->category) {
            $query->whereHas('categories', fn($q)=>$q->where('slug', $request->category));
        }

        if ($request->color) {
            $query->whereHas('colors', fn($q)=>$q->where('color', $request->color));
        }

        if ($request->size) {
            $query->whereHas('sizes', fn($q)=>$q->where('size', $request->size));
        }

        if ($request->feed) {
            $query->whereHas('feedItems', fn($q)=>$q->whereHas('feed', fn($f)=>$f->where('code',$request->feed)));
        }

        $products = $query->paginate(12);

        return ProductResource::collection($products);
    }



}
