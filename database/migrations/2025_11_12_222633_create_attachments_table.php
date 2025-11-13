<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('file')->nullable(); // الصورة أو الملف
            $table->string('product_name')->nullable(); // اسم المنتج
            $table->string('product_brand')->nullable(); // الماركة
            $table->decimal('price', 10, 2)->nullable(); // السعر
            $table->string('store_name')->nullable(); // منين اشترى
            $table->text('note')->nullable(); // أي ملاحظات إضافية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
