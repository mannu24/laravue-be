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
        // Add new fields
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'body')) {
                $table->text('body')->nullable()->after('title');
            }
            $table->text('ai_generated_summary')->nullable()->after('body');
            if (!Schema::hasColumn('questions', 'views')) {
                $table->unsignedInteger('views')->default(0)->after('ai_generated_summary');
            }
        });
        
        // Rename content to body if content exists and body doesn't
        if (Schema::hasColumn('questions', 'content') && !Schema::hasColumn('questions', 'body')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->renameColumn('content', 'body');
            });
        }
        
        // Rename view_count to views if view_count exists and views doesn't
        if (Schema::hasColumn('questions', 'view_count') && !Schema::hasColumn('questions', 'views')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->renameColumn('view_count', 'views');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['body', 'ai_generated_summary', 'views']);
        });
    }
};
