<?php

namespace App\Repositories;

use App\Models\Technology;
use Illuminate\Support\Collection;

class TechnologyRepository
{
    protected Technology $model;

    public function __construct(Technology $model)
    {
        $this->model = $model;
    }

    public function getAllActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function findOrCreateByName(string $name, int $createdById): Technology
    {
        return $this->model->firstOrCreate(
            ['name' => trim($name)],
            ['created_by_id' => $createdById, 'is_active' => true]
        );
    }

    public function processTechnologyNames(array $technologyNames, int $createdById): array
    {
        if (is_string($technologyNames)) {
            $technologyNames = array_map('trim', explode(',', $technologyNames));
        }

        $technologyIds = [];
        foreach ($technologyNames as $techName) {
            $technology = $this->findOrCreateByName($techName, $createdById);
            $technologyIds[] = $technology->id;
        }

        return $technologyIds;
    }
}
