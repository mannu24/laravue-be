<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'project_type' => $this->project_type,
            'github_url' => $this->github_url,
            'demo_url' => $this->demo_url,
            'is_sellable' => $this->is_sellable,
            'original_price' => $this->original_price,
            'selling_price' => $this->selling_price,
            'views' => $this->views,
            'is_active' => $this->is_active,
            'featured_image' => $this->featured_image,
            'upvotes_count' => $this->upvotes_count,
            'is_upvoted_by_user' => $this->is_upvoted_by_user,
            // Status & Workflow
            'status' => $this->status,
            'published_at' => $this->published_at,
            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'excerpt' => $this->excerpt,
            // Content
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'features' => $this->features,
            'requirements' => $this->requirements,
            'installation_guide' => $this->installation_guide,
            'changelog' => $this->changelog,
            'version' => $this->version,
            'license_type' => $this->license_type,
            'license_url' => $this->license_url,
            'documentation_url' => $this->documentation_url,
            'support_url' => $this->support_url,
            // Maintenance
            'language' => $this->language,
            'update_frequency' => $this->update_frequency,
            'is_maintained' => $this->is_maintained,
            'maintenance_status' => $this->maintenance_status,
            'deprecation_notice' => $this->deprecation_notice,
            'migration_guide_url' => $this->migration_guide_url,
            // Commerce Advanced
            'delivery_method' => $this->delivery_method,
            'affiliate_enabled' => $this->affiliate_enabled,
            'affiliate_commission' => $this->affiliate_commission,
            // Analytics
            'unique_views' => $this->unique_views,
            'downloads_count' => $this->downloads_count,
            'purchases_count' => $this->purchases_count,
            'avg_rating' => $this->avg_rating,
            'ratings_count' => $this->ratings_count,
            'bookmarks_count' => $this->bookmarks_count,
            'trending_score' => $this->trending_score,
            // Commerce
            'currency' => $this->currency,
            'discount_percentage' => $this->discount_percentage,
            'is_on_discount' => $this->isOnDiscount(),
            'discounted_price' => $this->getDiscountedPrice(),
            'is_featured' => $this->is_featured,
            'sales_count' => $this->sales_count,
            'is_in_stock' => $this->isInStock(),
            // Quality
            'is_verified' => $this->is_verified,
            'is_premium' => $this->is_premium,
            // Categorization
            'difficulty_level' => $this->difficulty_level,
            'estimated_build_time' => $this->estimated_build_time,
            'industry' => $this->industry,
            'tags' => $this->tags,
            // Social
            'reviews_count' => $this->reviews_count,
            'stars_count' => $this->stars_count,
            'forks_count' => $this->forks_count,
            // Version
            'current_version' => $this->current_version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                    'profile_photo' => $this->user->profile_photo,
                ];
            }),

            'technologies' => $this->whenLoaded('technologies', function () {
                return $this->technologies->map(function ($technology) {
                    return [
                        'id' => $technology->id,
                        'name' => $technology->name,
                    ];
                });
            }),

            'category' => $this->whenLoaded('category', function () {
                return $this->category ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ] : null;
            }),

            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'user' => $review->relationLoaded('user') && $review->user ? [
                            'id' => $review->user->id,
                            'name' => $review->user->name,
                        ] : null,
                        'created_at' => $review->created_at,
                    ];
                });
            }),

            'versions' => $this->whenLoaded('versions', function () {
                return $this->versions->map(function ($version) {
                    return [
                        'id' => $version->id,
                        'version_number' => $version->version_number,
                        'changelog' => $version->changelog,
                        'release_date' => $version->release_date,
                        'is_stable' => $version->is_stable,
                    ];
                });
            }),

            'funds' => $this->whenLoaded('funds', function () {
                return $this->funds->map(function ($fund) {
                    return [
                        'id' => $fund->id,
                        'amount' => optional($fund->transaction)->amount,
                        'user' => $fund->relationLoaded('user') && $fund->user ? [
                            'id' => $fund->user->id,
                            'name' => $fund->user->name,
                            'username' => $fund->user->username,
                        ] : null,
                        'created_at' => $fund->created_at,
                    ];
                });
            }),
        ];
    }
}
