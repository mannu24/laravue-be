<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_admin to users table
        if (!Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_admin')->default(false)->after('email');
            });
        }

        // Portfolio templates (registered in DB for admin management)
        Schema::create('portfolio_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Subscription plans
        Schema::create('portfolio_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('duration_months');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('max_projects')->nullable(); // null = unlimited
            $table->json('features')->nullable();
            $table->boolean('allows_custom_domain')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Coupon codes
        Schema::create('portfolio_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);
            $table->unsignedInteger('max_uses')->nullable(); // null = unlimited
            $table->unsignedInteger('max_uses_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->json('applicable_plans')->nullable(); // null = all plans
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Orders (Razorpay)
        Schema::create('portfolio_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('portfolio_plans');
            $table->foreignId('coupon_id')->nullable()->constrained('portfolio_coupons')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // Coupon usage tracking
        Schema::create('portfolio_coupon_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('portfolio_coupons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained('portfolio_orders')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
        });

        // Subscriptions
        Schema::create('portfolio_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('portfolio_plans');
            $table->foreignId('order_id')->constrained('portfolio_orders');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('grace_ends_at')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled', 'refunded'])->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });

        // Main portfolio record
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subdomain')->unique();
            $table->string('template_slug')->default('minimal');
            $table->boolean('is_published')->default(false);
            $table->string('title')->nullable();
            $table->string('tagline')->nullable();
            $table->text('bio')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_country')->nullable();
            $table->boolean('available_for_hire')->default(false);
            $table->string('resume_path')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        // Portfolio social links
        Schema::create('portfolio_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('platform');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Portfolio skills
        Schema::create('portfolio_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('name');
            $table->enum('proficiency', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Work experience
        Schema::create('portfolio_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('company');
            $table->string('role');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Education
        Schema::create('portfolio_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('field')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Portfolio projects
        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->json('tech_stack')->nullable();
            $table->string('live_url')->nullable();
            $table->string('source_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Testimonials
        Schema::create('portfolio_testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('author_name');
            $table->string('author_role')->nullable();
            $table->string('author_company')->nullable();
            $table->text('content');
            $table->string('author_photo_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Custom sections (Pro/Annual)
        Schema::create('portfolio_custom_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Custom domains
        Schema::create('portfolio_custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('portfolios')->onDelete('cascade');
            $table->string('domain')->unique();
            $table->enum('type', ['root', 'subdomain'])->default('root');
            $table->enum('status', ['pending', 'verified', 'failed'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('dns_error')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_custom_domains');
        Schema::dropIfExists('portfolio_custom_sections');
        Schema::dropIfExists('portfolio_testimonials');
        Schema::dropIfExists('portfolio_projects');
        Schema::dropIfExists('portfolio_educations');
        Schema::dropIfExists('portfolio_experiences');
        Schema::dropIfExists('portfolio_skills');
        Schema::dropIfExists('portfolio_social_links');
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('portfolio_subscriptions');
        Schema::dropIfExists('portfolio_coupon_uses');
        Schema::dropIfExists('portfolio_orders');
        Schema::dropIfExists('portfolio_coupons');
        Schema::dropIfExists('portfolio_plans');
        Schema::dropIfExists('portfolio_templates');

        if (Schema::hasColumn('users', 'is_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }
};
