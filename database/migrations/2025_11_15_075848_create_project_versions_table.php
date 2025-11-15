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
        Schema::create('project_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('version_number', 50);
            $table->text('changelog')->nullable();
            $table->date('release_date');
            $table->string('download_url', 500)->nullable();
            $table->boolean('is_stable')->default(false);
            $table->timestamps();
            
            $table->index('project_id');
            $table->index('version_number');
            $table->index('is_stable');
            $table->index('release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_versions');
    }
};
