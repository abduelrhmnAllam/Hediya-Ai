<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occasion_names', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المناسبة (مثلاً: Birthday)
            $table->string('type')->nullable(); // نوعها البرمجي (مثلاً: birthday)
            $table->text('description')->nullable(); // وصف بسيط
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occasion_names');
    }
};
