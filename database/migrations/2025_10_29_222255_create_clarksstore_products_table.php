<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clarksstore_products', function (Blueprint $table) {
            $table->id();
            $table->string('offer_id')->unique(); 
            $table->foreignId('category_id')->nullable()->constrained('clarksstore_categories')->onDelete('set null');

            $table->string('vendor')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('old_price', 10, 2)->nullable();
            $table->string('currency', 5)->default('AED');
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('gender')->nullable();
            $table->text('picture')->nullable();
            $table->text('url')->nullable();
            $table->boolean('available')->default(true);
            $table->timestamp('modified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clarksstore_products');
    }
};
