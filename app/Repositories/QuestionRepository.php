<?php

namespace App\Repositories;

use App\Models\Question;
use App\Models\Upvote;
use Illuminate\Container\Attributes\Auth;

class QuestionRepository
{
    protected Question $model;

    public function __construct(Question $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with(['user', 'upvotes', 'likes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function upvotes()
    {
        return $this->morphMany(Upvote::class, 'record');
    }

    public function getLatest()
    {
        return $this->model->with(['user', 'upvotes', 'likes'])->latest()->take(10)->get();
    }

    public function findById($id)
    {
        return $this->model->with(['user', 'upvotes'])->findOrFail($id);
    }

    public function findBySlug($slug)
    {
        return $this->model->with(['user', 'upvotes', 'tags'])->where('slug', $slug)->firstOrFail();
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

        if ($question->user_id === Auth()->id()) {
            throw new \Exception("You cannot upvote your own question.");
        }

        if ($question->upvotes()->where('user_id', $userId)->exists()) {
            throw new \Exception("You have already upvoted this question.");
        }

        $question->upvotes()->create(['user_id' => $userId]);
    }
}
