<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use App\Repositories\TechnologyRepository;
use App\Repositories\ProjectFundRepository;
use App\Repositories\ProjectCategoryRepository;
use App\Repositories\ProjectReviewRepository;
use App\Repositories\ProjectVersionRepository;
use App\Traits\TracksViewsWithRateLimit;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    use TracksViewsWithRateLimit;
    protected ProjectRepository $projectRepository;
    protected TechnologyRepository $technologyRepository;
    protected ProjectFundRepository $projectFundRepository;
    protected ProjectCategoryRepository $categoryRepository;
    protected ProjectReviewRepository $reviewRepository;
    protected ProjectVersionRepository $versionRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        TechnologyRepository $technologyRepository,
        ProjectFundRepository $projectFundRepository,
        ProjectCategoryRepository $categoryRepository,
        ProjectReviewRepository $reviewRepository,
        ProjectVersionRepository $versionRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->technologyRepository = $technologyRepository;
        $this->projectFundRepository = $projectFundRepository;
        $this->categoryRepository = $categoryRepository;
        $this->reviewRepository = $reviewRepository;
        $this->versionRepository = $versionRepository;
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

    public function getProjectBySlugWithRelations(string $slug): Project
    {
        return $this->projectRepository->findBySlugWithRelations($slug);
    }

    public function createProject(array $data): Project
    {
        // Set default status to draft if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'draft';
        }

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

        return $project->load(['user', 'technologies', 'category']);
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

    public function incrementProjectViews(Project $project, Request $request, int $rateLimitMinutes = 5): bool
    {
        // Check rate limit before incrementing
        if (!$this->trackViewWithRateLimit('project', $project->getAttribute('id'), $request, $rateLimitMinutes)) {
            return false; // Rate limited, view not counted
        }
        
        $this->projectRepository->incrementViews($project);
        return true; // View counted
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

    // Status Management
    public function publishProject(Project $project): Project
    {
        $project->publish();
        return $project->fresh();
    }

    public function rejectProject(Project $project, string $reason = null): Project
    {
        $project->reject($reason);
        return $project->fresh();
    }

    public function archiveProject(Project $project): Project
    {
        $project->archive();
        return $project->fresh();
    }

    public function submitForReview(Project $project): Project
    {
        $project->submitForReview();
        return $project->fresh();
    }

    // Category Management
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getRootCategories(): Collection
    {
        return $this->categoryRepository->getRootCategories();
    }

    public function getCategory(int $id)
    {
        return $this->categoryRepository->findById($id);
    }

    public function getProjectsByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator
    {
        return $this->projectRepository->getByCategory($categoryId, $perPage);
    }

    // Review Management
    public function createReview(int $projectId, array $data): \App\Models\ProjectReview
    {
        $data['project_id'] = $projectId;
        $data['user_id'] = Auth::id();
        
        $review = $this->reviewRepository->create($data);
        
        // Update project rating
        $project = $this->projectRepository->findById($projectId);
        $project->updateRating();
        
        return $review->load('user');
    }

    public function getProjectReviews(int $projectId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewRepository->getByProject($projectId, $perPage);
    }

    public function updateReview(int $reviewId, array $data): \App\Models\ProjectReview
    {
        $review = $this->reviewRepository->findById($reviewId);
        $review = $this->reviewRepository->update($review, $data);
        
        // Update project rating
        $review->getRelation('project')->updateRating();
        
        return $review->load('user');
    }

    public function deleteReview(int $reviewId): bool
    {
        $review = $this->reviewRepository->findById($reviewId);
        $project = $review->getRelation('project');
        
        $deleted = $this->reviewRepository->delete($review);
        
        // Update project rating
        $project->updateRating();
        
        return $deleted;
    }

    // Version Management
    public function createVersion(int $projectId, array $data): \App\Models\ProjectVersion
    {
        $data['project_id'] = $projectId;
        $version = $this->versionRepository->create($data);
        
        // Update project current version
        $project = $this->projectRepository->findById($projectId);
        $project->update([
            'current_version' => $data['version_number'],
            'latest_version_released_at' => $data['release_date'],
        ]);
        
        return $version->load('project');
    }

    public function getProjectVersions(int $projectId): Collection
    {
        return $this->versionRepository->getByProject($projectId);
    }

    // Featured & Trending
    public function getFeaturedProjects(int $limit = 10): Collection
    {
        return $this->projectRepository->getFeatured($limit);
    }

    public function getTrendingProjects(int $limit = 10): Collection
    {
        return $this->projectRepository->getTrending($limit);
    }

    // Drafts & Pending
    public function getDrafts(int $perPage = 12): LengthAwarePaginator
    {
        return $this->projectRepository->getDrafts(Auth::id(), $perPage);
    }

    public function getPendingProjects(int $perPage = 12): LengthAwarePaginator
    {
        return $this->projectRepository->getPending($perPage);
    }

    // Quality Management
    public function verifyProject(Project $project): Project
    {
        $project->verify();
        return $project->fresh();
    }

    public function featureProject(Project $project, $until = null): Project
    {
        $project->feature($until);
        return $project->fresh();
    }

    public function unfeatureProject(Project $project): Project
    {
        $project->unfeature();
        return $project->fresh();
    }

    // Analytics
    public function incrementUniqueViews(Project $project, Request $request, int $rateLimitMinutes = 5): bool
    {
        // Note: This method assumes rate limit was already checked in incrementProjectViews
        // We still check here as a safety measure, but the cache key is the same
        // so if regular views were incremented, this will also pass
        if (!$this->trackViewWithRateLimit('project', $project->getAttribute('id'), $request, $rateLimitMinutes)) {
            return false; // Rate limited, view not counted
        }
        
        $project->incrementUniqueViews();
        return true; // View counted
    }

    public function incrementDownloads(Project $project): void
    {
        $project->incrementDownloads();
    }

    public function incrementPurchases(Project $project): void
    {
        $project->incrementPurchases();
    }
}
