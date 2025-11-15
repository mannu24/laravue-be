<?php

namespace App\Repositories;

use App\Models\ProjectCategory;
use Illuminate\Support\Collection;

class ProjectCategoryRepository
{
    protected ProjectCategory $model;

    public function __construct(ProjectCategory $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->active()->ordered()->get();
    }

    public function getRootCategories(): Collection
    {
        return $this->model->active()->root()->ordered()->get();
    }

    public function getCategoryWithChildren(int $id): ProjectCategory
    {
        return $this->model->with('children')->findOrFail($id);
    }

    public function findById(int $id): ProjectCategory
    {
        return $this->model->findOrFail($id);
    }

    public function findBySlug(string $slug): ProjectCategory
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): ProjectCategory
    {
        return $this->model->create($data);
    }

    public function update(ProjectCategory $category, array $data): ProjectCategory
    {
        $category->update($data);
        return $category;
    }

    public function delete(ProjectCategory $category): bool
    {
        return $category->delete();
    }
}

