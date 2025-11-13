<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('avatars', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // اسم افتراضي أو وصفي
            $table->string('image'); // رابط الصورة
            $table->enum('gender', ['male', 'female', 'neutral'])->default('neutral'); // نوع الصورة
            $table->boolean('is_default')->default(false); // ممكن نحدد الافتراضي
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avatars');
    }
};
