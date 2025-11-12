<?php
// database/migrations/2025_11_09_000900_create_feed_profiles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feed_profiles', function (Blueprint $t) {
            $t->id();
            $t->string('feed_code')->unique()->index();
            $t->string('map_item_tag')->default('offer'); // عنصر المنتج الأساسي
            $t->string('map_sku');        // يسمح بـ @attribute
            $t->string('map_title');
            $t->string('map_price')->nullable();
            $t->string('map_brand')->nullable();
            $t->string('map_category_path')->nullable(); // لو في شجرة نصية
            $t->json('extra')->nullable(); // باقي selectors: images, currency, url, qty, category_guid, params...
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('feed_profiles');
    }
};
