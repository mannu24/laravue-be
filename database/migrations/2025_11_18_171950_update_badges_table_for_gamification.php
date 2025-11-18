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
        Schema::table('badges', function (Blueprint $table) {
            $table->string('slug')->unique()->after('name');
            $table->string('type')->after('description');
            $table->unsignedInteger('xp_reward')->default(0)->after('type');
        });
        
        // Rename icon to icon_path if icon column exists
        if (Schema::hasColumn('badges', 'icon') && !Schema::hasColumn('badges', 'icon_path')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->renameColumn('icon', 'icon_path');
            });
        } elseif (!Schema::hasColumn('badges', 'icon_path')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->string('icon_path')->nullable()->after('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['slug', 'type', 'icon_path', 'xp_reward']);
        });
    }
};
