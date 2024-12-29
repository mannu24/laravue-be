<?php

use App\Enum\ContactStatusTypes;
use App\Enum\PaymentStatusTypes;
use App\Enum\ProjectTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('developer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('bio')->nullable();
            $table->string('github_username')->nullable();
            $table->string('linkedin_username')->nullable();
            $table->string('website')->nullable();
            $table->string('avatar')->nullable();
            $table->integer('reputation_points')->default(0);
            $table->integer('credits_balance')->default(500);
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('icon');
            $table->boolean('is_active');
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('awarded_at');
            $table->timestamps();
        });

        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_active');
            $table->timestamps();
        });

        Schema::create('developer_technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('tech_id')->nullable()->constrained('technologies')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title',500);
            $table->string('slug',500)->unique();
            $table->longText('description');
            $table->enum('project_type', ProjectTypes::toArray())->default(ProjectTypes::OPEN);
            $table->string('github_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->boolean('is_sellable')->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->integer('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('project_technologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('technology_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_id');
            $table->decimal('amount', 10, 2);
            $table->string('mode');
            $table->string('status');
            $table->enum('payment_status', PaymentStatusTypes::toArray())->default(PaymentStatusTypes::PENDING);
            $table->timestamps();
        });

        Schema::create('project_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->constrained('transactions')->nullable()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('post_code')->comment('used_for_routes');
            $table->string('title', 1000)->nullable();
            $table->text('meta_content')->nullable();
            $table->longText('content');
            $table->integer('views')->default(0);
            $table->boolean('is_ai_generated')->default(0);
            $table->boolean('is_blocked')->default(0);
            $table->timestamps();
        });

        Schema::create('mentions', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('position');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('tag_associates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->morphs('record'); //blogs/posts/questions
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('record');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('record');
            $table->timestamps();
        });

        Schema::create('upvotes', function (Blueprint $table) {
            $table->id();
            $table->morphs('record');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->morphs('record');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('content_html')->nullable(); // For rendered HTML content
            $table->boolean('is_solved')->default(false);
            $table->integer('score')->default(0); // Combined score of upvotes/downvotes
            $table->integer('view_count')->default(0);
            $table->timestamp('last_activity_date')->nullable();
            $table->string('source')->nullable(); // 'stackoverflow', 'github', 'internal'
            $table->string('source_url')->nullable();
            $table->string('source_question_id')->nullable(); // Original SO question ID
            $table->boolean('is_closed')->default(false);
            $table->string('closed_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->text('content_html')->nullable(); // For rendered HTML content
            $table->boolean('is_accepted')->default(false);
            $table->integer('score')->default(0); // Combined score of upvotes/downvotes
            $table->integer('comment_count')->default(0);
            $table->timestamp('last_activity_date')->nullable();
            $table->string('source')->nullable(); // 'stackoverflow', 'github', 'internal'
            $table->string('source_url')->nullable();
            $table->string('source_answer_id')->nullable(); // Original SO answer ID
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['follower_id', 'following_id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reportable');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->enum('status', ContactStatusTypes::toArray())->default(ContactStatusTypes::PENDING);
            $table->timestamps();
        });

        Schema::create('contact_queries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ContactStatusTypes::toArray())->default(ContactStatusTypes::PENDING);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('pages') ;
        Schema::dropIfExists('contact_queries') ;
        Schema::dropIfExists('reports') ;
        Schema::dropIfExists('followers') ;
        Schema::dropIfExists('answers') ;
        Schema::dropIfExists('questions') ;
        Schema::dropIfExists('bookmarks') ;
        Schema::dropIfExists('upvotes') ;
        Schema::dropIfExists('likes') ;
        Schema::dropIfExists('comments') ;
        Schema::dropIfExists('tag_associates') ;
        Schema::dropIfExists('tags') ;
        Schema::dropIfExists('mentions') ;
        Schema::dropIfExists('posts') ;
        Schema::dropIfExists('project_funds') ;
        Schema::dropIfExists('transactions') ;
        Schema::dropIfExists('project_technologies') ;
        Schema::dropIfExists('projects') ;
        Schema::dropIfExists('developer_technologies') ;
        Schema::dropIfExists('technologies') ;
        Schema::dropIfExists('user_badges') ;
        Schema::dropIfExists('badges') ;
        Schema::dropIfExists('developer_profiles') ;
    }
};
