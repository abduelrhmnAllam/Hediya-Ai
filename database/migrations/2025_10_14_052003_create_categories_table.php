<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();          // من XML
            $table->string('name')->nullable();               // اسم الفئة
            $table->string('slug')->nullable();               // slug من الاسم
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete(); // فئة الأب
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
