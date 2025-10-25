<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 🎁 ميزانية المستخدم للهدايا
            $table->decimal('gift_budgets', 10, 2)->nullable()->after('password');

            // 🛍️ معدل الشراء (مثلاً "weekly", "monthly")
            $table->string('often_buy')->nullable()->after('gift_budgets');

            // ✅ هل المستخدم أكمل البيانات أو لا
            $table->boolean('is_completed')->default(false)->after('often_buy');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gift_budgets', 'often_buy', 'is_completed']);
        });
    }
};
