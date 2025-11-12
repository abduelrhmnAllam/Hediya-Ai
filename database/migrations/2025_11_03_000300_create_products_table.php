<?php

// database/migrations/2025_11_03_000300_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
         // احذف الجدول لو موجود بالفعل
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');                 // <name>
            $t->string('original_name')->nullable(); // <original_name>
            $t->string('fingerprint', 64)->unique(); // hash(brand+name)
            $t->string('master_sku')->nullable()->index(); // لو ثابت
            $t->text('short_description')->nullable();
            $t->longText('long_description')->nullable();
            $t->string('material')->nullable();
            $t->string('gender')->nullable();   // من <param name="gender">
            $t->string('status')->default('active'); // active/draft/hidden
            $t->json('attributes')->nullable(); // أي params إضافية
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('products');
    }
};
