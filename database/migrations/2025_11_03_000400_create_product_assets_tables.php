<?php
// database/migrations/2025_11_03_000400_create_product_assets_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('url');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['product_id','sort_order']);
        });

        Schema::create('product_colors', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('color'); // مثال: "Black" من <param name="color">
            $t->timestamps();
            $t->unique(['product_id','color']);
        });

        Schema::create('product_sizes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('size'); // مثال: 39، 40، 41 أو "One size"
            $t->timestamps();
            $t->unique(['product_id','size']);
        });
    Schema::create('product_categories', function (Blueprint $t) {
    $t->id();
    $t->foreignId('product_id')->constrained()->cascadeOnDelete();
    $t->foreignId('category_id')->constrained()->cascadeOnDelete();
    $t->timestamps();  
    $t->unique(['product_id','category_id']);
});


    }
    public function down(): void {
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('product_images');
    }
};

