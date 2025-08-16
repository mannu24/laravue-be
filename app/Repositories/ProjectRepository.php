<?php

namespace App\Repositories;

use App\Models\Project;
use App\Http\Traits\HttpResponse;

class ProjectRepository
{
    use HttpResponse;
    protected Project $model;

    public function __construct(Project $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->paginate(10);
    }

    public function getLatest()
    {
        return $this->model->with('user', 'upvotes')->latest()->take(10)->get();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($project, array $data)
    {
        $project->update($data);
        return $project;
    }

    public function delete($id)
    {
        $question = $this->model->findOrFail($id);
        $question->delete();
    }

    public function upvote($id, $userId)
    {
        $question = $this->model->findOrFail($id);

        if ($question->upvotes()->where('user_id', $userId)->exists()) {
            throw new \Exception('User has already upvoted this question.');
        }

        $question->upvotes()->create(['user_id' => $userId]);
    }
}
