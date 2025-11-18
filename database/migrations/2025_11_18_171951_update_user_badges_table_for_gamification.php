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
        Schema::table('user_badges', function (Blueprint $table) {
            // Ensure awarded_at exists and is nullable if not already set
            if (!Schema::hasColumn('user_badges', 'awarded_at')) {
                $table->timestamp('awarded_at')->nullable()->after('badge_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_badges', function (Blueprint $table) {
            if (Schema::hasColumn('user_badges', 'awarded_at')) {
                $table->dropColumn('awarded_at');
            }
        });
    }
};
