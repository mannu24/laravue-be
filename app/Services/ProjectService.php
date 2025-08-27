<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Repositories\TechnologyRepository;
use App\Repositories\ProjectFundRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    protected ProjectRepository $projectRepository;
    protected TechnologyRepository $technologyRepository;
    protected ProjectFundRepository $projectFundRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        TechnologyRepository $technologyRepository,
        ProjectFundRepository $projectFundRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->technologyRepository = $technologyRepository;
        $this->projectFundRepository = $projectFundRepository;
    }

    public function getAllProjects(array $filters = [], string $sort = 'recent', int $perPage = 12): LengthAwarePaginator
    {
        return $this->projectRepository->getAll($filters, $sort, $perPage);
    }

    public function getLatestProjects(): Collection
    {
        return $this->projectRepository->getLatest();
    }

    public function getProjectById(int $id): Project
    {
        return $this->projectRepository->findById($id);
    }

    public function getProjectByIdWithRelations(int $id): Project
    {
        return $this->projectRepository->findByIdWithRelations($id);
    }

    public function createProject(array $data): Project
    {
        $project = $this->projectRepository->create($data);

        // Handle technologies if provided
        if (isset($data['technologies']) && !empty($data['technologies'])) {
            $technologyIds = $this->technologyRepository->processTechnologyNames(
                $data['technologies'],
                Auth::id()
            );
            $this->projectRepository->attachTechnologies($project, $technologyIds);
        }

        // Handle featured image if provided
        if (isset($data['featured_image']) && $data['featured_image']) {
            $this->projectRepository->addFeaturedImage($project, $data['featured_image']);
        }

        return $project->load(['user', 'technologies']);
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project = $this->projectRepository->update($project, $data);

        // Handle technologies if provided
        if (isset($data['technologies']) && !empty($data['technologies'])) {
            $technologyIds = $this->technologyRepository->processTechnologyNames(
                $data['technologies'],
                Auth::id()
            );
            $this->projectRepository->syncTechnologies($project, $technologyIds);
        }

        // Handle featured image if provided
        if (isset($data['featured_image']) && $data['featured_image']) {
            $this->projectRepository->updateFeaturedImage($project, $data['featured_image']);
        }

        return $project->load(['user', 'technologies']);
    }

    public function deleteProject(Project $project): bool
    {
        return $this->projectRepository->delete($project);
    }

    public function upvoteProject(int $projectId): bool
    {
        return $this->projectRepository->upvote($projectId, Auth::id());
    }

    public function fundProject(int $projectId, float $amount, string $mode = 'manual'): array
    {
        return $this->projectFundRepository->createFunding($projectId, Auth::id(), $amount, $mode);
    }

    public function incrementProjectViews(Project $project): void
    {
        $this->projectRepository->incrementViews($project);
    }

    public function isProjectOwner(Project $project): bool
    {
        return $this->projectRepository->isOwner($project, Auth::id());
    }

    public function getAllTechnologies(): Collection
    {
        return $this->technologyRepository->getAllActive();
    }

    public function createTechnology(string $name)
    {
        return $this->technologyRepository->findOrCreateByName($name, Auth::id());
    }
}
