<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Technology;
use App\Http\Traits\HttpResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectRepository
{
    use HttpResponse;
    protected Project $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters = [], string $sort = 'recent', int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'technologies', 'upvotes', 'category']);
        
        // Show published projects to everyone, and draft/pending projects to their owners
        $userId = auth()->id();
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('status', 'published')
                  ->orWhere(function ($subQ) use ($userId) {
                      $subQ->whereIn('status', ['draft', 'pending'])
                           ->where('user_id', $userId);
                  });
            });
        } else {
            // For non-authenticated users, only show published projects
            $query->published();
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('short_description', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('excerpt', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply type filter
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            if ($filters['type'] === 'open') {
                $query->openSource();
            } elseif ($filters['type'] === 'sellable') {
                $query->sellable();
            }
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        // Apply difficulty filter
        if (!empty($filters['difficulty'])) {
            $query->byDifficulty($filters['difficulty']);
        }

        // Apply industry filter
        if (!empty($filters['industry'])) {
            $query->byIndustry($filters['industry']);
        }

        // Apply user_id filter
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Apply featured filter
        if (isset($filters['featured']) && $filters['featured']) {
            $query->featured();
        }

        // Apply verified filter
        if (isset($filters['verified']) && $filters['verified']) {
            $query->verified();
        }

        // Apply premium filter
        if (isset($filters['premium']) && $filters['premium']) {
            $query->premium();
        }

        // Apply discount filter
        if (isset($filters['on_discount']) && $filters['on_discount']) {
            $query->onDiscount();
        }

        // Apply technology filter
        if (!empty($filters['technology'])) {
            $query->whereHas('technologies', function (Builder $q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['technology'] . '%');
            });
        }

        // Apply sorting
        $this->applySorting($query, $sort);

        return $query->paginate($perPage);
    }

    public function getLatest(): Collection
    {
        return $this->model->with('user', 'upvotes')->latest()->take(10)->get();
    }

    public function findById(int $id): Project
    {
        return $this->model->findOrFail($id);
    }

    public function findBySlug(string $slug): Project
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function findByIdWithRelations(int $id): Project
    {
        return $this->model->with(['user', 'technologies', 'upvotes', 'category', 'reviews.user', 'versions'])
            ->findOrFail($id);
    }

    public function findBySlugWithRelations(string $slug): Project
    {
        return $this->model->with(['user', 'technologies', 'funds.user', 'funds.transaction', 'upvotes', 'category', 'reviews.user', 'versions'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function create(array $data): Project
    {
        return $this->model->create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project;
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function upvote(int $projectId, int $userId): bool
    {
        $project = $this->model->findOrFail($projectId);
        return $project->toggleUpvote($userId);
    }

    public function incrementViews(Project $project): void
    {
        $project->incrementViews();
    }

    public function attachTechnologies(Project $project, array $technologyIds): void
    {
        $project->technologies()->attach($technologyIds);
    }

    public function syncTechnologies(Project $project, array $technologyIds): void
    {
        $project->technologies()->sync($technologyIds);
    }

    public function addFeaturedImage(Project $project, $file): void
    {
        $project->addMediaFromRequest('featured_image')
               ->toMediaCollection('featured_image');
    }

    public function updateFeaturedImage(Project $project, $file): void
    {
        $project->clearMediaCollection('featured_image');
        $project->addMediaFromRequest('featured_image')
               ->toMediaCollection('featured_image');
    }

    public function isOwner(Project $project, int $userId): bool
    {
        return $project->getAttribute('user_id') === $userId;
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return $this->model->with(['user', 'technologies', 'category'])
            ->featured()
            ->published()
            ->orderBy('featured_rank')
            ->limit($limit)
            ->get();
    }

    public function getTrending(int $limit = 10): Collection
    {
        return $this->model->with(['user', 'technologies', 'category'])
            ->trending()
            ->published()
            ->limit($limit)
            ->get();
    }

    public function getByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->with(['user', 'technologies', 'category'])
            ->byCategory($categoryId)
            ->published()
            ->latest()
            ->paginate($perPage);
    }

    public function getDrafts(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->draft()
            ->latest()
            ->paginate($perPage);
    }

    public function getPending(int $perPage = 12): LengthAwarePaginator
    {
        return $this->model->with(['user', 'category'])
            ->pending()
            ->latest()
            ->paginate($perPage);
    }

    private function applySorting(Builder $query, string $sort): void
    {
        switch ($sort) {
            case 'popular':
                $query->popular();
                break;
            case 'trending':
                $query->trending();
                break;
            case 'recent':
                $query->recent();
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            case 'rating':
                $query->orderBy('avg_rating', 'desc')->orderBy('ratings_count', 'desc');
                break;
            case 'price_low':
                $query->orderBy('selling_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('selling_price', 'desc');
                break;
            default:
                $query->recent();
        }
    }
}
