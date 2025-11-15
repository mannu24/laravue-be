<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Helper function to add column if it doesn't exist
            $addColumnIfNotExists = function($columnName, $callback) use ($table) {
                if (!Schema::hasColumn('projects', $columnName)) {
                    $callback($table);
                }
            };

            // Status & Workflow
            $addColumnIfNotExists('status', fn($t) => $t->enum('status', ['draft', 'pending', 'published', 'archived', 'rejected'])->default('draft')->after('is_active'));
            $addColumnIfNotExists('published_at', fn($t) => $t->timestamp('published_at')->nullable()->after('status'));
            $addColumnIfNotExists('rejected_at', fn($t) => $t->timestamp('rejected_at')->nullable()->after('published_at'));
            $addColumnIfNotExists('rejection_reason', fn($t) => $t->text('rejection_reason')->nullable()->after('rejected_at'));
            
            // SEO
            $addColumnIfNotExists('meta_title', fn($t) => $t->string('meta_title', 255)->nullable()->after('rejection_reason'));
            $addColumnIfNotExists('meta_description', fn($t) => $t->text('meta_description')->nullable()->after('meta_title'));
            $addColumnIfNotExists('meta_keywords', fn($t) => $t->string('meta_keywords', 500)->nullable()->after('meta_description'));
            $addColumnIfNotExists('excerpt', fn($t) => $t->text('excerpt')->nullable()->after('meta_keywords'));
            
            // Content Enhancement
            $addColumnIfNotExists('short_description', fn($t) => $t->string('short_description', 500)->nullable()->after('excerpt'));
            $addColumnIfNotExists('long_description', fn($t) => $t->longText('long_description')->nullable()->after('short_description'));
            $addColumnIfNotExists('features', fn($t) => $t->json('features')->nullable()->after('long_description'));
            $addColumnIfNotExists('requirements', fn($t) => $t->text('requirements')->nullable()->after('features'));
            $addColumnIfNotExists('installation_guide', fn($t) => $t->text('installation_guide')->nullable()->after('requirements'));
            $addColumnIfNotExists('changelog', fn($t) => $t->text('changelog')->nullable()->after('installation_guide'));
            $addColumnIfNotExists('version', fn($t) => $t->string('version', 50)->nullable()->after('changelog'));
            $addColumnIfNotExists('license_type', fn($t) => $t->string('license_type', 100)->nullable()->after('version'));
            $addColumnIfNotExists('license_url', fn($t) => $t->string('license_url', 500)->nullable()->after('license_type'));
            $addColumnIfNotExists('documentation_url', fn($t) => $t->string('documentation_url', 500)->nullable()->after('license_url'));
            $addColumnIfNotExists('support_url', fn($t) => $t->string('support_url', 500)->nullable()->after('documentation_url'));
            
            // Media Counts
            $addColumnIfNotExists('screenshot_count', fn($t) => $t->integer('screenshot_count')->default(0)->after('support_url'));
            $addColumnIfNotExists('video_count', fn($t) => $t->integer('video_count')->default(0)->after('screenshot_count'));
            $addColumnIfNotExists('gallery_count', fn($t) => $t->integer('gallery_count')->default(0)->after('video_count'));
            
            // Analytics & Engagement
            $addColumnIfNotExists('unique_views', fn($t) => $t->integer('unique_views')->default(0)->after('gallery_count'));
            $addColumnIfNotExists('downloads_count', fn($t) => $t->integer('downloads_count')->default(0)->after('unique_views'));
            $addColumnIfNotExists('purchases_count', fn($t) => $t->integer('purchases_count')->default(0)->after('downloads_count'));
            $addColumnIfNotExists('conversion_rate', fn($t) => $t->decimal('conversion_rate', 5, 2)->default(0.00)->after('purchases_count'));
            $addColumnIfNotExists('avg_rating', fn($t) => $t->decimal('avg_rating', 3, 2)->nullable()->after('conversion_rate'));
            $addColumnIfNotExists('ratings_count', fn($t) => $t->integer('ratings_count')->default(0)->after('avg_rating'));
            $addColumnIfNotExists('comments_count', fn($t) => $t->integer('comments_count')->default(0)->after('ratings_count'));
            $addColumnIfNotExists('shares_count', fn($t) => $t->integer('shares_count')->default(0)->after('comments_count'));
            $addColumnIfNotExists('bookmarks_count', fn($t) => $t->integer('bookmarks_count')->default(0)->after('shares_count'));
            $addColumnIfNotExists('last_viewed_at', fn($t) => $t->timestamp('last_viewed_at')->nullable()->after('bookmarks_count'));
            $addColumnIfNotExists('trending_score', fn($t) => $t->decimal('trending_score', 10, 2)->default(0.00)->after('last_viewed_at'));
            
            // Commerce
            $addColumnIfNotExists('currency', fn($t) => $t->string('currency', 3)->default('USD')->after('trending_score'));
            $addColumnIfNotExists('discount_percentage', fn($t) => $t->decimal('discount_percentage', 5, 2)->nullable()->after('currency'));
            $addColumnIfNotExists('discount_start_date', fn($t) => $t->date('discount_start_date')->nullable()->after('discount_percentage'));
            $addColumnIfNotExists('discount_end_date', fn($t) => $t->date('discount_end_date')->nullable()->after('discount_start_date'));
            $addColumnIfNotExists('is_featured', fn($t) => $t->boolean('is_featured')->default(false)->after('discount_end_date'));
            $addColumnIfNotExists('featured_until', fn($t) => $t->timestamp('featured_until')->nullable()->after('is_featured'));
            $addColumnIfNotExists('sales_count', fn($t) => $t->integer('sales_count')->default(0)->after('featured_until'));
            $addColumnIfNotExists('revenue', fn($t) => $t->decimal('revenue', 10, 2)->default(0.00)->after('sales_count'));
            $addColumnIfNotExists('commission_rate', fn($t) => $t->decimal('commission_rate', 5, 2)->default(0.00)->after('revenue'));
            $addColumnIfNotExists('affiliate_enabled', fn($t) => $t->boolean('affiliate_enabled')->default(false)->after('commission_rate'));
            $addColumnIfNotExists('affiliate_commission', fn($t) => $t->decimal('affiliate_commission', 5, 2)->nullable()->after('affiliate_enabled'));
            $addColumnIfNotExists('stock_quantity', fn($t) => $t->integer('stock_quantity')->nullable()->after('affiliate_commission'));
            $addColumnIfNotExists('is_digital', fn($t) => $t->boolean('is_digital')->default(true)->after('stock_quantity'));
            $addColumnIfNotExists('delivery_method', fn($t) => $t->string('delivery_method', 50)->nullable()->after('is_digital'));
            
            // Quality & Moderation
            $addColumnIfNotExists('is_verified', fn($t) => $t->boolean('is_verified')->default(false)->after('delivery_method'));
            $addColumnIfNotExists('quality_score', fn($t) => $t->integer('quality_score')->default(0)->after('is_verified'));
            $addColumnIfNotExists('moderation_status', fn($t) => $t->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending')->after('quality_score'));
            $addColumnIfNotExists('moderated_at', fn($t) => $t->timestamp('moderated_at')->nullable()->after('moderation_status'));
            $addColumnIfNotExists('moderated_by', fn($t) => $t->unsignedBigInteger('moderated_by')->nullable()->after('moderated_at'));
            $addColumnIfNotExists('moderation_notes', fn($t) => $t->text('moderation_notes')->nullable()->after('moderated_by'));
            $addColumnIfNotExists('spam_score', fn($t) => $t->integer('spam_score')->default(0)->after('moderation_notes'));
            $addColumnIfNotExists('is_premium', fn($t) => $t->boolean('is_premium')->default(false)->after('spam_score'));
            
            // Categorization
            $addColumnIfNotExists('category_id', fn($t) => $t->unsignedBigInteger('category_id')->nullable()->after('is_premium'));
            $addColumnIfNotExists('difficulty_level', fn($t) => $t->enum('difficulty_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable()->after('category_id'));
            $addColumnIfNotExists('estimated_build_time', fn($t) => $t->string('estimated_build_time', 50)->nullable()->after('difficulty_level'));
            $addColumnIfNotExists('industry', fn($t) => $t->string('industry', 100)->nullable()->after('estimated_build_time'));
            $addColumnIfNotExists('tags', fn($t) => $t->json('tags')->nullable()->after('industry'));
            
            // UX
            $addColumnIfNotExists('language', fn($t) => $t->string('language', 10)->default('en')->after('tags'));
            $addColumnIfNotExists('read_time', fn($t) => $t->integer('read_time')->nullable()->after('language'));
            $addColumnIfNotExists('last_updated_at', fn($t) => $t->timestamp('last_updated_at')->nullable()->after('read_time'));
            $addColumnIfNotExists('update_frequency', fn($t) => $t->string('update_frequency', 50)->nullable()->after('last_updated_at'));
            $addColumnIfNotExists('is_maintained', fn($t) => $t->boolean('is_maintained')->default(true)->after('update_frequency'));
            $addColumnIfNotExists('maintenance_status', fn($t) => $t->string('maintenance_status', 50)->nullable()->after('is_maintained'));
            $addColumnIfNotExists('deprecation_notice', fn($t) => $t->text('deprecation_notice')->nullable()->after('maintenance_status'));
            $addColumnIfNotExists('migration_guide_url', fn($t) => $t->string('migration_guide_url', 500)->nullable()->after('deprecation_notice'));
            
            // Social
            $addColumnIfNotExists('reviews_count', fn($t) => $t->integer('reviews_count')->default(0)->after('migration_guide_url'));
            $addColumnIfNotExists('discussions_count', fn($t) => $t->integer('discussions_count')->default(0)->after('reviews_count'));
            $addColumnIfNotExists('contributors_count', fn($t) => $t->integer('contributors_count')->default(0)->after('discussions_count'));
            $addColumnIfNotExists('stars_count', fn($t) => $t->integer('stars_count')->default(0)->after('contributors_count'));
            $addColumnIfNotExists('forks_count', fn($t) => $t->integer('forks_count')->default(0)->after('stars_count'));
            
            // Performance
            $addColumnIfNotExists('cache_key', fn($t) => $t->string('cache_key', 100)->nullable()->after('forks_count'));
            $addColumnIfNotExists('indexed_at', fn($t) => $t->timestamp('indexed_at')->nullable()->after('cache_key'));
            $addColumnIfNotExists('popularity_score', fn($t) => $t->decimal('popularity_score', 10, 2)->default(0.00)->after('indexed_at'));
            $addColumnIfNotExists('trending_rank', fn($t) => $t->integer('trending_rank')->nullable()->after('popularity_score'));
            $addColumnIfNotExists('featured_rank', fn($t) => $t->integer('featured_rank')->nullable()->after('trending_rank'));
            $addColumnIfNotExists('last_calculated_at', fn($t) => $t->timestamp('last_calculated_at')->nullable()->after('featured_rank'));
            
            // Version
            $addColumnIfNotExists('current_version', fn($t) => $t->string('current_version', 50)->nullable()->after('last_calculated_at'));
            $addColumnIfNotExists('latest_version_released_at', fn($t) => $t->timestamp('latest_version_released_at')->nullable()->after('current_version'));
            
            // Legal
            $addColumnIfNotExists('terms_accepted_at', fn($t) => $t->timestamp('terms_accepted_at')->nullable()->after('latest_version_released_at'));
            $addColumnIfNotExists('privacy_policy_url', fn($t) => $t->string('privacy_policy_url', 500)->nullable()->after('terms_accepted_at'));
            $addColumnIfNotExists('terms_of_service_url', fn($t) => $t->string('terms_of_service_url', 500)->nullable()->after('privacy_policy_url'));
            $addColumnIfNotExists('gdpr_compliant', fn($t) => $t->boolean('gdpr_compliant')->default(false)->after('terms_of_service_url'));
            $addColumnIfNotExists('data_retention_days', fn($t) => $t->integer('data_retention_days')->nullable()->after('gdpr_compliant'));
            $addColumnIfNotExists('age_restriction', fn($t) => $t->integer('age_restriction')->nullable()->after('data_retention_days'));
            $addColumnIfNotExists('geographic_restrictions', fn($t) => $t->json('geographic_restrictions')->nullable()->after('age_restriction'));
            
            // Notifications
            $addColumnIfNotExists('notification_preferences', fn($t) => $t->json('notification_preferences')->nullable()->after('geographic_restrictions'));
            $addColumnIfNotExists('last_notification_sent_at', fn($t) => $t->timestamp('last_notification_sent_at')->nullable()->after('notification_preferences'));
            $addColumnIfNotExists('subscribers_count', fn($t) => $t->integer('subscribers_count')->default(0)->after('last_notification_sent_at'));
        });

        // Add foreign keys separately (after tables exist)
        Schema::table('projects', function (Blueprint $table) {
            // Add foreign key for moderated_by if it doesn't exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'projects' 
                AND CONSTRAINT_NAME = 'projects_moderated_by_foreign'
            ");
            
            if (empty($foreignKeys) && Schema::hasColumn('projects', 'moderated_by')) {
                $table->foreign('moderated_by')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Add indexes if they don't exist
        Schema::table('projects', function (Blueprint $table) {
            $indexes = ['status', 'published_at', 'category_id', 'is_featured', 'is_verified', 'is_premium', 'created_at'];
            foreach ($indexes as $index) {
                if (Schema::hasColumn('projects', $index)) {
                    try {
                        $table->index($index);
                    } catch (\Exception $e) {
                        // Index might already exist, ignore
                    }
                }
            }
            
            // Composite indexes
            if (Schema::hasColumn('projects', 'trending_score') && Schema::hasColumn('projects', 'published_at')) {
                try {
                    $table->index(['trending_score', 'published_at']);
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
            
            if (Schema::hasColumn('projects', 'popularity_score') && Schema::hasColumn('projects', 'published_at')) {
                try {
                    $table->index(['popularity_score', 'published_at']);
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign keys if they exist
            try {
                $table->dropForeign(['moderated_by']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            // Drop columns
            $columns = [
                'status', 'published_at', 'rejected_at', 'rejection_reason',
                'meta_title', 'meta_description', 'meta_keywords', 'excerpt',
                'short_description', 'long_description', 'features', 'requirements',
                'installation_guide', 'changelog', 'version', 'license_type',
                'license_url', 'documentation_url', 'support_url',
                'screenshot_count', 'video_count', 'gallery_count',
                'unique_views', 'downloads_count', 'purchases_count', 'conversion_rate',
                'avg_rating', 'ratings_count', 'comments_count', 'shares_count',
                'bookmarks_count', 'last_viewed_at', 'trending_score',
                'currency', 'discount_percentage', 'discount_start_date', 'discount_end_date',
                'is_featured', 'featured_until', 'sales_count', 'revenue',
                'commission_rate', 'affiliate_enabled', 'affiliate_commission',
                'stock_quantity', 'is_digital', 'delivery_method',
                'is_verified', 'quality_score', 'moderation_status', 'moderated_at',
                'moderated_by', 'moderation_notes', 'spam_score', 'is_premium',
                'category_id', 'difficulty_level', 'estimated_build_time', 'industry', 'tags',
                'language', 'read_time', 'last_updated_at', 'update_frequency',
                'is_maintained', 'maintenance_status', 'deprecation_notice', 'migration_guide_url',
                'reviews_count', 'discussions_count', 'contributors_count', 'stars_count', 'forks_count',
                'cache_key', 'indexed_at', 'popularity_score', 'trending_rank', 'featured_rank',
                'last_calculated_at', 'current_version', 'latest_version_released_at',
                'terms_accepted_at', 'privacy_policy_url', 'terms_of_service_url',
                'gdpr_compliant', 'data_retention_days', 'age_restriction', 'geographic_restrictions',
                'notification_preferences', 'last_notification_sent_at', 'subscribers_count'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (\Exception $e) {
                        // Column might not exist or have dependencies
                    }
                }
            }
        });
    }
};
