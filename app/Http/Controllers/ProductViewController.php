<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductViewController extends Controller
{
    /**
     * عرض جميع الفئات والمنتجات حسب الـ Feed
     */
    public function index()
    {
        $feeds = \App\Models\Feed::with([
            'feedProducts.product.categories',
            'feedProducts.product.images',
        ])->get();

        $feedsData = $feeds->map(function ($feed) {
            $categories = [];

            foreach ($feed->feedProducts as $feedProduct) {
                $product = $feedProduct->product;
                if (!$product) continue;

                foreach ($product->categories as $category) {
                    if (!isset($categories[$category->id])) {
                        $categories[$category->id] = [
                            'id' => $category->id,
                            'name' => $category->name,
                            'products' => [],
                        ];
                    }

                    $image = $product->images->sortBy('sort_order')->first()?->url;

                    // تنظيف السعر
                    $price = $this->sanitizePrice($feedProduct->price);
                    $currency = $feedProduct->currency ?? 'EGP';

                    $categories[$category->id]['products'][] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $price,
                        'currency' => $currency,
                        'sku' => $feedProduct->sku ?? '-',
                        'url' => $feedProduct->url ?? '#',
                        'pictures' => $image ? [$image] : [],
                    ];
                }
            }

            return [
                'feed_name' => $feed->name,
                'categories' => array_values($categories),
            ];
        });

        return view('products-by-feed', compact('feedsData'));
    }

    /**
     * عرض المنتجات داخل فئة معينة مجمعة حسب الـ Feed
     */
    public function getCategoryTree($id)
    {
        $category = Category::with([
            'children.children',
            'products.brand',
            'products.images',
            'products.feedItems.feed'
        ])->findOrFail($id);

        $grouped = [];

        foreach ($category->products as $product) {
            $feedItem = $product->feedItems->first();
            if (!$feedItem) continue;

            // تأكد من الـ raw JSON
            $raw = $feedItem->raw;
            if (is_string($raw)) {
                $raw = json_decode($raw, true);
            }

            $feedName = $feedItem->feed?->name ?? 'Other Feeds';
            if (!isset($grouped[$feedName])) {
                $grouped[$feedName] = [];
            }

            // تنظيف السعر
            $price = $feedItem->price ?? ($raw['price'] ?? 0);
            $price = $this->sanitizePrice($price);
            $currency = $feedItem->currency ?? ($raw['currency_id'] ?? 'EGP');

            $image = $product->images->sortBy('sort_order')->first()?->url
                ?? ($raw['image'] ?? ($raw['pictures'][0] ?? null));

            if ($image && !filter_var($image, FILTER_VALIDATE_URL)) {
                $image = null;
            }

            $grouped[$feedName][] = [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand?->name,
                'price' => $price,
                'currency_id' => $currency,
                'url' => $feedItem->url ?? '#',
                'sku' => $feedItem->sku ?? $product->sku ?? 'N/A',
                'pictures' => $image ? [$image] : [],
            ];

            // Log مفيد للمتابعة
            Log::info('Product Loaded', [
                'feed' => $feedName,
                'product' => $product->name,
                'price' => $price,
                'currency' => $currency,
                'image' => $image,
            ]);
        }

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children,
                'feeds' => $grouped,
            ]
        ]);
    }

    /**
     * عرض كل المنتجات داخل فئة معينة
     */
    public function getProductsByCategory($id)
    {
        $products = Product::with([
                'brand',
                'images',
                'feedItems' => fn($q) => $q->select('id', 'product_id', 'price', 'currency', 'url', 'raw')
            ])
            ->whereHas('categories', fn($q) => $q->where('id', $id))
            ->get()
            ->map(function ($product) {
                $feed = $product->feedItems->first();
                if (!$feed) return null;

                $raw = $feed->raw;
                if (is_string($raw)) {
                    $raw = json_decode($raw, true);
                }

                $price = $feed->price ?? ($raw['price'] ?? 0);
                $price = $this->sanitizePrice($price);
                $currency = $feed->currency ?? ($raw['currency_id'] ?? 'EGP');

                $image = $product->images->sortBy('sort_order')->first()?->url
                    ?? ($raw['image'] ?? ($raw['pictures'][0] ?? null));

                if ($image && !filter_var($image, FILTER_VALIDATE_URL)) {
                    $image = null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'price' => $price,
                    'currency_id' => $currency,
                    'url' => $feed->url ?? '#',
                    'pictures' => $image ? [$image] : [],
                ];
            })
            ->filter();

        return response()->json(['products' => $products->values()]);
    }

    /**
     * عرض تفاصيل المنتج
     */
public function show($id)
{
    $product = Product::with(['categories', 'brand', 'images', 'colors', 'sizes', 'feedItems'])
        ->findOrFail($id);

    $feed = $product->feedItems->first();

    // تنظيف السعر من أي فواصل أو رموز
    $price = $this->sanitizePrice($feed?->price ?? 0);
    $oldPrice = $this->sanitizePrice($feed?->old_price ?? null);
    $currency = $feed?->currency ?? $feed?->currency_id ?? 'EGP';
    $sku = $feed?->sku ?? $product->sku ?? 'غير متوفر';
    $url = $feed?->url ?? '#';

    $mainImage = $product->images->sortBy('sort_order')->first()?->url;
    $otherImages = $product->images->sortBy('sort_order')->pluck('url')->toArray();

    return view('product-details', compact(
        'product', 'feed', 'price', 'oldPrice', 'currency', 'sku', 'url', 'mainImage', 'otherImages'
    ));
}

private function sanitizePrice($price)
{
    if (is_null($price)) return 0;

    $price = trim((string)$price);
    $price = str_replace(',', '', $price);
    $price = preg_replace('/[^\d.]/', '', $price);

    return (float) $price ?: 0;
}

}
