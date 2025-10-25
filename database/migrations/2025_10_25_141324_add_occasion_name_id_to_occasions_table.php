<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('occasions', function (Blueprint $table) {
            $table->foreignId('occasion_name_id')
                  ->nullable()
                  ->constrained('occasion_names')
                  ->onDelete('set null')
                  ->after('person_id');
        });
    }

    public function down(): void
    {
        Schema::table('occasions', function (Blueprint $table) {
            $table->dropForeign(['occasion_name_id']);
            $table->dropColumn('occasion_name_id');
        });
    }
};
