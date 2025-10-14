<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // من بيانات XML
            $table->string('external_id')->nullable();           // offer id
            $table->boolean('available')->default(true);         // available="true"
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('currency_id')->nullable();           // currencyId
            $table->text('name')->nullable();                    // name
            $table->longText('description')->nullable();         // description
            $table->decimal('price', 10, 2)->nullable();         // price
            $table->decimal('old_price', 10, 2)->nullable();     // oldprice
            $table->string('vendor')->nullable();                // vendor
            $table->text('url')->nullable();                     // url
            $table->json('pictures')->nullable();                // جميع الصور بصيغة JSON
            $table->string('identifier_exists')->nullable();     // identifier_exists
            $table->bigInteger('modified_time')->nullable();     // modified_time

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
