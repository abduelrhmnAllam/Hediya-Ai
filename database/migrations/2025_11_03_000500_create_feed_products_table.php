<?php
// database/migrations/2025_11_03_000500_create_feed_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feed_products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete(); // master
            $t->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $t->foreignId('feed_run_id')->constrained()->cascadeOnDelete();

            $t->string('feed_offer_id')->index(); // من <offer id="...">
            $t->string('sku')->nullable()->index(); // من android_url/ios_url/param او id=...
            $t->string('currency', 8)->nullable(); // <currencyId>
            $t->decimal('price', 14, 2)->nullable();
            $t->decimal('old_price', 14, 2)->nullable();
            $t->boolean('available')->default(true);
            $t->integer('qty_actual')->nullable();
            $t->integer('size_count')->nullable();
            $t->timestamp('modified_time')->nullable(); // من <modified_time> (seconds epoch؟ خزنه كتاريخ لو حوّلته)
            $t->string('url', 1024)->nullable();       // <url>
            $t->string('brand_page_url', 512)->nullable();
            $t->string('cat1_name')->nullable();
            $t->string('cat2_name')->nullable();
            $t->string('cat3_name')->nullable();

            $t->json('raw')->nullable(); // snapshot من سطر الـ XML بعد parsING
            $t->timestamps();

            $t->unique(['feed_run_id','feed_offer_id']);
            $t->index(['feed_id','feed_offer_id']); // للاستعلام السريع عبر feed
        });
    }
    public function down(): void {
        Schema::dropIfExists('feed_products');
    }
};
