<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;

class XmlSeeder extends Seeder
{
    public function run(): void
    {
        // 📂 مسار ملف الـ XML (غيّره حسب مكان الملف عندك)
        $filePath = storage_path('app/saudi-arabia-ar_products_20251012_202930.xml');

        if (!file_exists($filePath)) {
            $this->command->error("❌ ملف XML غير موجود في المسار: {$filePath}");
            return;
        }

        $xml = simplexml_load_file($filePath);

        $this->command->info('🚀 بدء استيراد الفئات (Categories)...');

        // ✅ استيراد الفئات
        foreach ($xml->shop->categories->category as $cat) {
            $externalId = (string) $cat['id'];
            $parentExternalId = (string) $cat['parentId'] ?? null;

            // نحاول نلاقي الأب لو موجود
            $parent = null;
            if ($parentExternalId) {
                $parent = Category::where('external_id', $parentExternalId)->first();
            }

            Category::updateOrCreate(
                ['external_id' => $externalId],
                [
                    'name' => (string) $cat,
                    'slug' => Str::slug((string) $cat),
                    'parent_id' => $parent?->id,
                ]
            );
        }

        $this->command->info('✅ تم استيراد الفئات بنجاح.');

        // ✅ استيراد المنتجات
        $this->command->info('🚀 بدء استيراد المنتجات (Products)...');

        foreach ($xml->shop->offers->offer as $prod) {

            // استخراج الصور
            $pictures = [];
            foreach ($prod->picture as $img) {
                $pictures[] = (string) $img;
            }

            // ربط الفئة حسب external_id
            $category = Category::where('external_id', (string) $prod->categoryId)->first();

            Product::updateOrCreate(
    ['external_id' => (string) $prod['id']],
    [
        'available'          => ((string) $prod['available'] === 'true'),
        'category_id'        => $category?->id,
        'currency_id'        => (string) $prod->currencyId,
        'name'               => (string) $prod->name,
        'description'        => (string) $prod->description,

        // ✅ تنظيف السعر قبل التخزين
        'price'              => (float) str_replace(',', '', (string) $prod->price),
        'old_price'          => (float) str_replace(',', '', (string) $prod->oldprice),

        'vendor'             => (string) $prod->vendor,
        'url'                => (string) $prod->url,
        'pictures'           => json_encode($pictures, JSON_UNESCAPED_UNICODE),
        'identifier_exists'  => (string) $prod->identifier_exists,
        'modified_time'      => (int) $prod->modified_time,
    ]
);

        }

        $this->command->info('✅ تم استيراد المنتجات بنجاح.');
    }
}
