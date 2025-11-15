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
        Schema::create('github_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('github_owner');
            $table->string('github_repo');
            $table->string('github_repo_id')->nullable();
            $table->string('github_full_name'); // owner/repo
            $table->json('imported_data')->nullable(); // Store original GitHub data for reference
            $table->timestamp('imported_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'github_full_name']); // Prevent duplicate imports per user
            $table->index('github_full_name');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('github_imports');
    }
};
