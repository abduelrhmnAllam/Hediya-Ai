<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('pic')->nullable();
            $table->date('birthday_date')->nullable();
            $table->foreignId('relative_id')->nullable()->constrained('relatives')->nullOnDelete();
            $table->integer('age')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->json('interests_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
   {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('interests_ids');
        });
    }
};
