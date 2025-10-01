<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occasions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('title');
            $table->date('date');
            $table->string('type')->nullable(); // birthday, wedding, graduation ...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occasions');
    }
};
