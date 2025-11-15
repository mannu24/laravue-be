<?php

namespace App\Repositories;

use App\Models\ProjectReview;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectReviewRepository
{
    protected ProjectReview $model;

    public function __construct(ProjectReview $model)
    {
        $this->model = $model;
    }

    public function getByProject(int $projectId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('project_id', $projectId)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function getFeaturedByProject(int $projectId): Collection
    {
        return $this->model->where('project_id', $projectId)
            ->featured()
            ->with('user')
            ->latest()
            ->get();
    }

    public function getByRating(int $projectId, int $rating): Collection
    {
        return $this->model->where('project_id', $projectId)
            ->byRating($rating)
            ->with('user')
            ->latest()
            ->get();
    }

    public function findById(int $id): ProjectReview
    {
        return $this->model->with(['project', 'user'])->findOrFail($id);
    }

    public function findByUserAndProject(int $userId, int $projectId): ?ProjectReview
    {
        return $this->model->where('user_id', $userId)
            ->where('project_id', $projectId)
            ->first();
    }

    public function create(array $data): ProjectReview
    {
        return $this->model->create($data);
    }

    public function update(ProjectReview $review, array $data): ProjectReview
    {
        $review->update($data);
        return $review;
    }

    public function delete(ProjectReview $review): bool
    {
        return $review->delete();
    }

    public function markAsHelpful(ProjectReview $review): void
    {
        $review->markAsHelpful();
    }

    public function markAsFeatured(ProjectReview $review): void
    {
        $review->markAsFeatured();
    }

    public function getAverageRating(int $projectId): float
    {
        return $this->model->where('project_id', $projectId)->avg('rating') ?? 0;
    }

    public function getRatingDistribution(int $projectId): array
    {
        return $this->model->where('project_id', $projectId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();
    }
}

