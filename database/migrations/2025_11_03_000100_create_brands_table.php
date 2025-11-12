<?php
// database/migrations/2025_11_03_000100_create_brands_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();      // من <vendor>
            $t->string('slug')->unique();
            $t->string('website')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('brands');
    }
};

