<?php

namespace App\Http\Controllers;

use App\Models\FeedRun;
use Illuminate\Support\Facades\DB;

class FeedRunController extends Controller
{
    public function index()
    {
        $runs = FeedRun::query()
            ->with('feed')
            ->latest()
            ->paginate(30);

        return view('feeds.runs.index', compact('runs'));
    }

public function show(FeedRun $run)
{
    // total offers in this run
    $total_products = DB::table('feed_products')
        ->where('feed_run_id',$run->id)
        ->count();

    // per brand count
    $brands = DB::table('feed_products as fp')
    ->join('products as p','fp.product_id','=','p.id')
    ->leftJoin('brands as b','p.brand_id','=','b.id')
    ->select(DB::raw('IFNULL(b.name,"— No brand —") as brand_name'), DB::raw('count(*) as cnt'))
    ->where('fp.feed_run_id',$run->id)
    ->groupBy('brand_name')
    ->orderByDesc('cnt')
    ->get();


  $categories = DB::table('feed_products as fp')
    ->join('products as p','p.id','=','fp.product_id')
    ->leftJoin('product_categories as pc','pc.product_id','=','p.id')
    ->leftJoin('categories as c','c.id','=','pc.category_id')
    ->select(DB::raw('IFNULL(c.name,"— No category —") as cat_name'), DB::raw('count(*) as cnt'))
    ->where('fp.feed_run_id',$run->id)
    ->where('p.status','active')   // هنا الشرط
    ->groupBy('cat_name')
    ->orderByDesc('cnt')
    ->get();


    return view('feeds.runs.show', compact('run','total_products','brands','categories'));
}

}
