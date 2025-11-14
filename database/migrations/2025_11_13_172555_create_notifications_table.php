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
        if (Schema::hasTable('notifications')) {
            return;
        }
        
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who receives the notification
            $table->foreignId('notifiable_id')->nullable(); // User who triggered the notification
            $table->string('type'); // post_liked, comment_added, question_answered, mention, follow, etc.
            $table->string('title'); // Notification title
            $table->text('message'); // Notification message
            $table->morphs('subject'); // subject_type, subject_id (what the notification is about)
            $table->json('data')->nullable(); // Additional data
            $table->boolean('read')->default(false);
            $table->boolean('email_sent')->default(false); // Track if email was sent
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'read', 'created_at']);
            $table->index(['type', 'created_at']);
            // Note: morphs('subject') already creates an index on subject_type and subject_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
