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
        // Make user_id nullable for AI-generated answers
        Schema::table('answers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        
        // Add new fields
        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'body')) {
                $table->text('body')->nullable()->after('question_id');
            }
            $table->boolean('is_ai_generated')->default(false)->after('body');
            $table->boolean('is_verified')->default(false)->after('is_ai_generated');
            
            $table->index('is_ai_generated');
            $table->index('is_verified');
        });
        
        // Rename content to body if content exists and body doesn't
        if (Schema::hasColumn('answers', 'content') && !Schema::hasColumn('answers', 'body')) {
            Schema::table('answers', function (Blueprint $table) {
                $table->renameColumn('content', 'body');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn(['body', 'is_ai_generated', 'is_verified']);
        });
    }
};
