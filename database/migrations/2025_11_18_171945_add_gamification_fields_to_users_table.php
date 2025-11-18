<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('xp_total')->default(0)->after('password');
            $table->unsignedBigInteger('level_id')->nullable()->after('xp_total');
            $table->unsignedInteger('streak_days')->default(0)->after('level_id');
            $table->timestamp('last_active_at')->nullable()->after('streak_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'level_id')) {
                $table->dropForeign(['level_id']);
            }
            $table->dropColumn(['xp_total', 'level_id', 'streak_days', 'last_active_at']);
        });
    }
};
