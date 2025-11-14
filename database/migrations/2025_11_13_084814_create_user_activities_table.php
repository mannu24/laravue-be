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
        if (Schema::hasTable('user_activities')) {
            return;
        }
        
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity_type'); // post_created, question_created, answer_created, comment_created, like_created, follow_created, etc.
            $table->morphs('subject'); // subject_type, subject_id (polymorphic - what the activity is about)
            $table->text('description')->nullable(); // Human-readable description
            $table->json('metadata')->nullable(); // Additional data (e.g., post title, question title, etc.)
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            // Note: morphs('subject') already creates an index on subject_type and subject_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
