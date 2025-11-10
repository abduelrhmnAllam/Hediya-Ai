<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('occasion_names', function (Blueprint $table) {
        $table->string('background_color')->nullable()->after('name');
        $table->string('image_background')->nullable()->after('background_color');
    });
}


    /**
     * Reverse the migrations.
     */
   public function down()
{
    Schema::table('occasion_names', function (Blueprint $table) {
        $table->dropColumn(['background_color','image_background']);
    });
}

};
