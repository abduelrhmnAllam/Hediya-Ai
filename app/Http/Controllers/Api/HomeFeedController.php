<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Feed;
use App\Models\Product;
use Illuminate\Http\Request;
class HomeFeedController extends Controller
{
   public function __invoke()
{
    $feeds = [
      "bashracare_sa",
      "levelshoes_sa",
      "magrabi_sa",
      "thedealoutlet_sa",
    ];

    $result = [];

    foreach($feeds as $code)
    {
        $feed = \App\Models\Feed::where('code',$code)->first();

        if(!$feed) continue;

        // count total products for this feed
        $count = Product::where('status','active')
    ->whereHas('feedItems', function($q) use($feed){
        $q->where('feed_id',$feed->id)->where('available',true);
    })
    ->count();


        // sample 3 products
       $products = Product::where('status','active')
    ->whereHas('feedItems', function($q) use($feed){
        $q->where('feed_id',$feed->id)->where('available',true);
    })
    ->with(['brand','images','colors','sizes','categories','feedItems'])
    ->limit(3)
    ->get();


        $result[$code] = [
            'count' => $count,
            'items' => \App\Http\Resources\ProductResource::collection($products)
        ];
    }

    return response()->json([
        'feeds' => $result
    ]);
}


    public function byCountry(Request $req)
{
    $country = strtoupper($req->query('country', 'SA'));

    // هات كل ال feeds اللي في الدولة المطلوبة
    $feeds = Feed::country($country)->pluck('id');

    // هات كل المنتجات اللي عندها feedItems لأي feed من دول
    $products = Product::where('status','active')
        ->whereHas('feedItems', function($q) use($feeds){
            $q->whereIn('feed_id', $feeds)->where('available', true);
        })
        ->with(['brand','images','colors','sizes','categories'])
        ->paginate(30);

    return ProductResource::collection($products);
}

}
