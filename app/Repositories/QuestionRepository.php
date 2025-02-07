<?php

namespace App\Repositories;

use App\Models\Question;
use App\Models\Upvote;

class QuestionRepository
{
    protected Question $model;

    public function __construct(Question $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with('user', 'upvotes')->paginate(10);
    }

    public function getLatest()
    {
        return $this->model->with('user', 'upvotes')->latest()->take(10)->get();
    }

    public function findById($id)
    {
        return $this->model->with(['user', 'upvotes'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $question = $this->model->findOrFail($id);
        $question->update($data);
        return $question;
    }

    public function delete($slug)
    {
        $question = $this->model->where('slug', $slug)->firstOrFail();
        $question->delete();
    }

    public function like_unlike($slug)
    {
        $question = $this->model->where('slug', $slug)->firstOrFail();
        $question->toggleLike();
        return $question->liked;
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
