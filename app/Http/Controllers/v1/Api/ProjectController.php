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

class ProjectController extends Controller
{
    use HttpResponse;

    protected $service;

    public function __construct(ProjectService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $projects = $this->service->getAll();
        return $this->success(
            data: ProjectResource::collection($projects),
            message: 'Projects fetched successfully',
        );
    }

    public function show($id)
    {
        $project = $this->service->getProjectById($id);
        return $this->success(
            data: new ProjectResource($project),
            message: 'Project fetched successfully'
        );
    }

    public function store(CreateProjectRequest $request)
    {
        $validated = $request->validated();
        $project = $this->service->createProject($validated);

        return $this->success(
            data: new ProjectResource($project),
            message: 'Project created successfully'
        );
    }

    public function update(Project $project, UpdateProjectRequest $request)
    {

        $validated = $request->validated();

        $project = $this->service->updateProject($project, $validated);
        return $this->success(
            data: new ProjectResource($project),
            message: 'Project updated successfully'
        );
    }

    public function destroy(Project $project)
    {
        $this->service->deleteProject($project);
        return $this->success(
            message: 'Project deleted successfully'
        );
    }

}
