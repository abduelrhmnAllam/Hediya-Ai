<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            // references 'attributes.id'
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');

            // references 'projects.id' (or any entity, but in your case: projects)
            $table->unsignedBigInteger('entity_id');

            $table->string('value', 255)->nullable();
            $table->timestamps();

            // Index usage
            $table->index('attribute_id');
            $table->index('entity_id');
            // Very common filter: find records where attribute_id = X and value = Y
            // Composite index helps
            $table->index(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
