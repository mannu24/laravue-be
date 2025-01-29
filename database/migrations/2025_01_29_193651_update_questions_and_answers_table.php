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
        Schema::table('questions', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->unsigned()->change();
        });
        Schema::table('answers', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });
        Schema::table('answers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });
    }
};
