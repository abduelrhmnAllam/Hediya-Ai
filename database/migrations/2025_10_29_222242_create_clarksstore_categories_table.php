<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clarksstore_categories', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // id القادم من XML
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clarksstore_categories');
    }
};
