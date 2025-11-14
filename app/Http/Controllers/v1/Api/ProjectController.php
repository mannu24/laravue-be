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
        ];

        $sort = $request->get('sort', 'recent');
        $perPage = $request->get('per_page', 12);

        $projects = $this->projectService->getAllProjects($filters, $sort, $perPage);

        return ProjectResource::collection($projects);
    }

    public function show($id)
    {
        $project = $this->projectService->getProjectByIdWithRelations($id);

        // Increment views
        $this->projectService->incrementProjectViews($project);

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project fetched successfully'
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
}
