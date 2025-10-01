<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->string('task_name');
            $table->date('date');
            $table->decimal('hours', 5, 2);

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            $table->timestamps();

            // Index date if you often query by date ranges or exact dates
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
