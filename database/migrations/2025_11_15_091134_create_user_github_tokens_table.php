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
        Schema::create('user_github_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('access_token'); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('token_expires_at')->nullable();
            $table->string('github_username')->nullable();
            $table->string('github_user_id')->nullable();
            $table->string('github_email')->nullable();
            $table->string('github_avatar_url')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamps();
            
            $table->unique('user_id'); // One token per user
            $table->index('github_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_github_tokens');
    }
};
