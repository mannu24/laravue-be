<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Projects\CreateProjectRequest;
use App\Http\Requests\v1\Projects\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Traits\HttpResponse;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use HttpResponse;

    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'type' => $request->get('type'),
            'technology' => $request->get('technology'),
            'status' => $request->get('status'),
            'category' => $request->get('category'),
            'difficulty' => $request->get('difficulty'),
            'industry' => $request->get('industry'),
            'user_id' => $request->get('user_id'),
            'featured' => $request->boolean('featured'),
            'verified' => $request->boolean('verified'),
            'premium' => $request->boolean('premium'),
            'on_discount' => $request->boolean('on_discount'),
        ];

        $sort = $request->get('sort', 'recent');
        $perPage = $request->get('per_page', 12);

        $projects = $this->projectService->getAllProjects($filters, $sort, $perPage);

        return ProjectResource::collection($projects);
    }

    public function featured()
    {
        $projects = $this->projectService->getFeaturedProjects();
        return $this->success(
            data: ProjectResource::collection($projects),
            message: 'Featured projects fetched successfully'
        );
    }

    public function trending()
    {
        $projects = $this->projectService->getTrendingProjects();
        return $this->success(
            data: ProjectResource::collection($projects),
            message: 'Trending projects fetched successfully'
        );
    }

    public function drafts()
    {
        $projects = $this->projectService->getDrafts();
        return $this->success(
            data: ProjectResource::collection($projects),
            message: 'Draft projects fetched successfully'
        );
    }

    public function show($slug, Request $request)
    {
        $project = $this->projectService->getProjectBySlugWithRelations($slug);

        // Increment views with rate limiting (5 minutes for projects)
        // Both regular and unique views use the same rate limit check
        if ($this->projectService->incrementProjectViews($project, $request, 5)) {
            // Only increment unique views if regular views were incremented
            $this->projectService->incrementUniqueViews($project, $request, 5);
        }

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project fetched successfully'
        );
    }

    public function publish(Project $project)
    {
        if (!$this->projectService->isProjectOwner($project)) {
            return $this->error('Unauthorized', 403);
        }

        $project = $this->projectService->publishProject($project);

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project published successfully'
        );
    }

    public function submitForReview(Project $project)
    {
        if (!$this->projectService->isProjectOwner($project)) {
            return $this->error('Unauthorized', 403);
        }

        $project = $this->projectService->submitForReview($project);

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project submitted for review successfully'
        );
    }

    public function download(Project $project)
    {
        $this->projectService->incrementDownloads($project);

        return $this->success(
            message: 'Download tracked successfully'
        );
    }

    public function store(CreateProjectRequest $request)
    {
        $validated = $request->validated();

        // Prepare data for service
        $projectData = $this->prepareProjectData($validated, $request);

        $project = $this->projectService->createProject($projectData);

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project created successfully'
        );
    }

    public function update(Project $project, UpdateProjectRequest $request)
    {
        // Check if user owns the project
        if (!$this->projectService->isProjectOwner($project)) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validated();

        // Prepare data for service
        $projectData = $this->prepareProjectData($validated, $request);

        $project = $this->projectService->updateProject($project, $projectData);

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project updated successfully'
        );
    }

    public function destroy(Project $project)
    {
        // Check if user owns the project
        if (!$this->projectService->isProjectOwner($project)) {
            return $this->error('Unauthorized', 403);
        }

        $this->projectService->deleteProject($project);

        return $this->success(
            message: 'Project deleted successfully'
        );
    }

    public function upvote(Project $project)
    {
        $result = $this->projectService->upvoteProject($project->id);

        return $this->success(
            data: [
                'is_upvoted' => $result,
                'upvotes_count' => $project->upvotes()->count()
            ],
            message: $result ? 'Project upvoted successfully' : 'Project upvote removed'
        );
    }

    public function fund(Request $request, Project $project)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'mode' => 'nullable|string'
        ]);

        $fundingData = $this->projectService->fundProject(
            $project->id,
            $validated['amount'],
            $validated['mode'] ?? 'manual'
        );

        return $this->success(
            data: $fundingData,
            message: 'Project funding initiated successfully'
        );
    }

    public function getTechnologies()
    {
        $technologies = $this->projectService->getAllTechnologies();

        return $this->success(
            data: $technologies,
            message: 'Technologies fetched successfully'
        );
    }

    public function createTechnology(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:technologies,name'
        ]);

        $technology = $this->projectService->createTechnology($validated['name']);

        return $this->success(
            data: $technology,
            message: 'Technology created successfully'
        );
    }

    /**
     * Prepare project data for service layer
     */
    private function prepareProjectData(array $validated, Request $request): array
    {
        $projectData = $validated;

        // Handle technologies array
        if ($request->has('technologies')) {
            $projectData['technologies'] = $request->input('technologies');
        }

        // Handle featured image
        if ($request->hasFile('featured_image')) {
            $projectData['featured_image'] = $request->file('featured_image');
        }

        return $projectData;
    }

    // Categories
    public function getCategories()
    {
        $categories = $this->projectService->getAllCategories();
        return $this->success(
            data: $categories,
            message: 'Categories fetched successfully'
        );
    }

    public function getCategory($id)
    {
        $category = $this->projectService->getCategory($id);
        return $this->success(
            data: $category,
            message: 'Category fetched successfully'
        );
    }

    public function getProjectsByCategory($categoryId, Request $request)
    {
        $perPage = $request->get('per_page', 12);
        $projects = $this->projectService->getProjectsByCategory($categoryId, $perPage);
        return ProjectResource::collection($projects);
    }

    // Reviews
    public function getReviews(Project $project, Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $reviews = $this->projectService->getProjectReviews($project->id, $perPage);
        return $this->success(
            data: $reviews,
            message: 'Reviews fetched successfully'
        );
    }

    public function createReview(Request $request, Project $project)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = $this->projectService->createReview($project->id, $validated);

        return $this->success(
            data: $review,
            message: 'Review created successfully'
        );
    }

    public function updateReview(Request $request, $reviewId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = $this->projectService->updateReview($reviewId, $validated);

        return $this->success(
            data: $review,
            message: 'Review updated successfully'
        );
    }

    public function deleteReview($reviewId)
    {
        $this->projectService->deleteReview($reviewId);
        return $this->success(
            message: 'Review deleted successfully'
        );
    }

    // Versions
    public function getVersions(Project $project)
    {
        $versions = $this->projectService->getProjectVersions($project->id);
        return $this->success(
            data: $versions,
            message: 'Versions fetched successfully'
        );
    }

    public function createVersion(Request $request, Project $project)
    {
        if (!$this->projectService->isProjectOwner($project)) {
            return $this->error('Unauthorized', 403);
        }

        $validated = $request->validate([
            'version_number' => 'required|string|max:50',
            'changelog' => 'nullable|string',
            'release_date' => 'required|date',
            'download_url' => 'nullable|url',
            'is_stable' => 'boolean',
        ]);

        $version = $this->projectService->createVersion($project->id, $validated);

        return $this->success(
            data: $version,
            message: 'Version created successfully'
        );
    }
}
