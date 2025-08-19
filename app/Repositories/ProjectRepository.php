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
        $query = $this->model->with(['user', 'technologies', 'upvotes'])->active();

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply type filter
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            if ($filters['type'] === 'open') {
                $query->openSource();
            } elseif ($filters['type'] === 'sellable') {
                $query->sellable();
            }
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

    public function findByIdWithRelations(int $id): Project
    {
        return $this->model->with(['user', 'technologies', 'funds.user', 'funds.transaction', 'upvotes'])
            ->findOrFail($id);
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
        return $project->user_id === $userId;
    }

    private function applySorting(Builder $query, string $sort): void
    {
        switch ($sort) {
            case 'popular':
                $query->popular();
                break;
            case 'recent':
                $query->recent();
                break;
            case 'views':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->recent();
        }
    }
}
