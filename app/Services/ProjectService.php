<?php

namespace App\Services;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    protected $repository;

    public function __construct(ProjectRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getLatestQuestions()
    {
        return $this->repository->getLatest();
    }

    public function getProjectById($id)
    {
        return $this->repository->findById($id);
    }

    public function createProject(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateProject(Project $project, array $data)
    {
        return $this->repository->update($project, $data);
    }

    public function deleteProject($project)
    {
        $project->delete();;
    }

    public function upvoteQuestion($id)
    {
        $this->repository->upvote($id, Auth::id());
    }
}
