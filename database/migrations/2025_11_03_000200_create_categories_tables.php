<?php
// database/migrations/2025_11_03_000200_create_categories_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
           // تعطيل قيود العلاقات مؤقتًا
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // حذف الجدول لو موجود
        Schema::dropIfExists('categories');

        // إعادة تفعيل القيود
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('supplier_guid')->index(); // من XML: <category id="...">
            $t->string('name');
            $t->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete();
            $t->string('slug')->index();
            $t->json('extra')->nullable();
            $t->timestamps();

            $t->unique(['supplier_guid']); // لكل شجرة GUID فريد
        });

        Schema::create('category_feed_maps', function (Blueprint $t) {
            // (اختياري) لو نفس الـ GUID يتكرر عبر feeds مختلفة
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $t->unique(['category_id','feed_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('category_feed_maps');
        Schema::dropIfExists('categories');
    }
};
