<?php

namespace App\Repositories;

use App\Models\ProjectVersion;
use Illuminate\Support\Collection;

class ProjectVersionRepository
{
    protected ProjectVersion $model;

    public function __construct(ProjectVersion $model)
    {
        $this->model = $model;
    }

    public function getByProject(int $projectId): Collection
    {
        return $this->model->where('project_id', $projectId)
            ->latest('release_date')
            ->get();
    }

    public function getStableByProject(int $projectId): Collection
    {
        return $this->model->where('project_id', $projectId)
            ->stable()
            ->latest('release_date')
            ->get();
    }

    public function getLatestByProject(int $projectId): ?ProjectVersion
    {
        return $this->model->where('project_id', $projectId)
            ->latest('release_date')
            ->first();
    }

    public function findById(int $id): ProjectVersion
    {
        return $this->model->with('project')->findOrFail($id);
    }

    public function create(array $data): ProjectVersion
    {
        return $this->model->create($data);
    }

    public function update(ProjectVersion $version, array $data): ProjectVersion
    {
        $version->update($data);
        return $version;
    }

    public function delete(ProjectVersion $version): bool
    {
        return $version->delete();
    }

    public function markAsStable(ProjectVersion $version): void
    {
        $version->markAsStable();
    }
}

