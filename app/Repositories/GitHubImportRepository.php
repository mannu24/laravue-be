<?php

namespace App\Repositories;

use App\Models\GitHubImport;
use Illuminate\Database\Eloquent\Collection;

class GitHubImportRepository
{
    protected GitHubImport $model;

    public function __construct(GitHubImport $model)
    {
        $this->model = $model;
    }

    /**
     * Check if repository is already imported.
     */
    public function isImported(int $userId, string $owner, string $repo): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('github_full_name', "{$owner}/{$repo}")
            ->exists();
    }

    /**
     * Get import by repository.
     */
    public function getByRepository(int $userId, string $owner, string $repo): ?GitHubImport
    {
        return $this->model->where('user_id', $userId)
            ->where('github_full_name', "{$owner}/{$repo}")
            ->first();
    }

    /**
     * Get import history for user.
     */
    public function getHistory(int $userId, int $limit = 10): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with('project')
            ->orderBy('imported_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all imported repository full names for user.
     */
    public function getImportedRepos(int $userId): array
    {
        return $this->model->where('user_id', $userId)
            ->pluck('github_full_name')
            ->toArray();
    }
}

