<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_link_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('icon')->nullable();
            $table->string('base_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User Social Links Table
        Schema::create('user_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('social_link_type_id')->constrained()->onDelete('cascade');
            $table->string('username')->index();
            $table->string('url')->unique();
            $table->integer('position')->default(0);
            $table->integer('clicks')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        // Insert common social platforms
        DB::table('social_link_types')->insert([
            ['name' => 'Twitter', 'icon' => 'fab fa-twitter', 'base_url' => 'https://twitter.com/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GitHub', 'icon' => 'fab fa-github', 'base_url' => 'https://github.com/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LinkedIn', 'icon' => 'fab fa-linkedin', 'base_url' => 'https://linkedin.com/in/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Facebook', 'icon' => 'fab fa-facebook', 'base_url' => 'https://facebook.com/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Instagram', 'icon' => 'fab fa-instagram', 'base_url' => 'https://instagram.com/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'YouTube', 'icon' => 'fab fa-youtube', 'base_url' => 'https://youtube.com/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Twitch', 'icon' => 'fab fa-twitch', 'base_url' => 'https://twitch.tv/', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Discord', 'icon' => 'fab fa-discord', 'base_url' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Website', 'icon' => 'fas fa-globe', 'base_url' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('user_social_links');
        Schema::dropIfExists('social_link_types');
    }
};
