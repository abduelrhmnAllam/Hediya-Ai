<?php

// database/migrations/2025_11_03_000000_create_feeds_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feeds', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();          // e.g. levelshoes_sa
            $t->enum('type', ['xml','api','csv'])->default('xml');
            $t->string('default_currency', 8)->nullable();
            $t->string('country_code', 8)->nullable(); // SA, AE, ...
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('feed_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $t->string('file_name')->nullable();
            $t->string('file_hash', 128)->nullable()->index();
            $t->timestamp('imported_at')->useCurrent();
            $t->json('meta')->nullable(); // any extra info
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('feed_runs');
        Schema::dropIfExists('feeds');
    }
};
