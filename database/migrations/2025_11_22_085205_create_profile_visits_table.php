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
        Schema::create('profile_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('visited_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('visited_at');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('visitor_id');
            $table->index('visited_user_id');
            $table->index('visited_at');
            // Prevent duplicate visits on the same day
            $table->unique(['visitor_id', 'visited_user_id', 'visited_at'], 'unique_daily_visit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_visits');
    }
};
