<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\ClarksstoreCategory;
use App\Models\ClarksstoreProduct;
use Carbon\Carbon;

class ImportClarksstoreProducts extends Command
{
    protected $signature = 'import:clarksstore-products {path}';
    protected $description = 'Import Clarksstore products and categories from an XML file';

    public function handle()
    {
        $path = $this->argument('path');

        if (!File::exists($path)) {
            $this->error("❌ File not found: {$path}");
            return Command::FAILURE;
        }

        $this->info("📦 Loading XML file...");
        $xml = simplexml_load_file($path);

        if (!$xml || !isset($xml->shop)) {
            $this->error("⚠️ Invalid XML structure!");
            return Command::FAILURE;
        }

        // 🏷️ Step 1: Import Categories
        $this->info("📂 Importing categories...");
        $countCats = 0;

        foreach ($xml->shop->categories->category as $cat) {
            ClarksstoreCategory::updateOrCreate(
                ['uuid' => (string) $cat['id']],
                ['name' => trim((string) $cat)]
            );
            $countCats++;
        }

        $this->info("✅ Imported {$countCats} categories.");

        // 👟 Step 2: Import Products
        $this->info("🛒 Importing products...");
        $countProducts = 0;

        foreach ($xml->shop->offers->offer as $offer) {
            $category = ClarksstoreCategory::where('uuid', (string) $offer->categoryId)->first();

            // استخراج القيم الأساسية
            $offerId = (string) $offer['id'];
            $available = ((string) $offer['available']) === 'true';
            $price = (float) $offer->price;
            $oldPrice = isset($offer->oldprice) ? (float) $offer->oldprice : null;
            $description = trim((string) $offer->description);
            $modifiedTime = isset($offer->modified_time)
                ? Carbon::createFromTimestamp((int) $offer->modified_time)
                : null;

            // قراءة الـ params (size, gender, color)
            $size = null;
            $gender = null;
            $color = null;

            foreach ($offer->param as $param) {
                $name = strtolower((string) $param['name']);
                $value = trim((string) $param);

                if ($name === 'size') $size = $value;
                elseif ($name === 'gender') $gender = $value;
                elseif ($name === 'color') $color = $value;
            }

            // تجميع الصور (قد يكون أكثر من صورة)
            $pictures = [];
            foreach ($offer->picture as $pic) {
                $pictures[] = (string) $pic;
            }

            ClarksstoreProduct::updateOrCreate(
                ['offer_id' => $offerId],
                [
                    'category_id'  => $category?->id,
                    'vendor'       => (string) $offer->vendor,
                    'name'         => (string) $offer->name,
                    'description'  => $description,
                    'price'        => $price,
                    'old_price'    => $oldPrice,
                    'currency'     => (string) $offer->currencyId,
                    'color'        => $color,
                    'size'         => $size,
                    'gender'       => $gender,
                    'picture'      => implode(',', $pictures),
                    'url'          => (string) $offer->url,
                    'available'    => $available,
                    'modified_at'  => $modifiedTime,
                ]
            );

            $countProducts++;
        }

        $this->info("✅ Imported {$countProducts} products successfully!");
        $this->info("🎯 All data imported into clarksstore_products and clarksstore_categories!");

        return Command::SUCCESS;
    }
}
