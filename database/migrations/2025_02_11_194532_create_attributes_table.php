<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // text, date, number, select, etc.
            $table->string('type');
            $table->json('options')->nullable(); // if needed for select-type
            $table->timestamps();

            // If you frequently fetch attributes by name:
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
