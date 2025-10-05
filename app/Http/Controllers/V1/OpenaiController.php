<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class OpenaiController extends Controller
{
    /**
     * POST /api/v1/search
     * Accepts natural language query (dummy parsing for testing)
     */
        protected $openAI;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

  public function search(Request $request)
{
    $request->validate([
        'q' => 'required|string|max:1000'
    ]);

    $userInput = $request->input('q');

    // 1. استخدم OpenAI لتحويل البحث لفلترة منظمة
    $filters = $this->openAI->parseToFilters($userInput);

    if (is_null($filters)) {
        return response()->json(['error' => 'Failed to parse query'], 500);
    }

    // 2. إنشاء query باستخدام Eloquent بطريقة آمنة
    $query = Product::query();

    // فلترة category
    if (!empty($filters['category'])) {
        $category = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $filters['category']);
        if ($category !== '') {
            $query->where('category', 'like', '%' . $category . '%'); // wildcard ليصبح أكثر مرونة
        }
    }

    // نطاق السعر
    if (!is_null($filters['min_price'])) {
        $query->where('price', '>=', floatval($filters['min_price']));
    }
    if (!is_null($filters['max_price'])) {
        $query->where('price', '<=', floatval($filters['max_price']));
    }

    // البحث بالكلمات المفتاحية داخل الاسم
    if (!empty($filters['keywords']) && is_array($filters['keywords'])) {
        $query->where(function($q) use ($filters) {
            foreach ($filters['keywords'] as $kw) {
                $kw = trim($kw);
                if ($kw === '') continue;
                $q->orWhere('name', 'like', '%' . $kw . '%');
            }
        });
    }

    // ترتيب النتائج - اختياري: حسب الأحدث أولاً
    $query->orderBy('created_at', 'desc');

    // تنفيذ البحث - الحد الأقصى للنتائج
    $results = $query->limit(50)->get();

    return response()->json([
        'filters' => $filters,
        'count' => $results->count(),
        'results' => $results,
    ]);
}

    /**
     * POST /api/v1/advanced-search
     * Accepts explicit filters plus optional query
     */
        public function advancedSearch(Request $request)
    {
        $data = $request->validate([
            'q' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
        ]);

        $query = Product::query();

        // If q is present, parse it and merge filters (OpenAI interpretation)
        if (!empty($data['q'])) {
            $filters = $this->openAI->parseToFilters($data['q']) ?? [];
        } else {
            $filters = [
                'category' => null,
                'min_price' => null,
                'max_price' => null,
                'keywords' => null,
            ];
        }

        // Merge explicit body filters (explicit wins over parsed)
        $category = $data['category'] ?? $filters['category'] ?? null;
        $min_price = $data['min_price'] ?? $filters['min_price'] ?? null;
        $max_price = $data['max_price'] ?? $filters['max_price'] ?? null;
        $keywords = $filters['keywords'] ?? null;

        if (!empty($category)) {
            $category = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $category);
            if ($category !== '') {
                $query->where('category', 'like', $category);
            }
        }

        if (!is_null($min_price)) {
            $query->where('price', '>=', floatval($min_price));
        }

        if (!is_null($max_price)) {
            $query->where('price', '<=', floatval($max_price));
        }

        if (!empty($keywords) && is_array($keywords)) {
            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if ($kw === '') continue;
                $query->where('name', 'like', '%' . $this->escapeLike($kw) . '%');
            }
        }

        $results = $query->limit(100)->get();

        return response()->json([
            'filters' => compact('category','min_price','max_price','keywords'),
            'count' => $results->count(),
            'results' => $results,
        ]);
    }

    /**
     * Escape LIKE wildcards for safer LIKE queries.
     * Note: Eloquent will still bind parameters to avoid injection.
     */
    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
