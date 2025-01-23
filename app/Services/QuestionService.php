<?php

namespace App\Services;

use App\Repositories\QuestionRepository;
use Illuminate\Support\Facades\Auth;

class QuestionService
{
    protected $repository;

    public function __construct(QuestionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllQuestions()
    {
        return $this->repository->getAll();
    }

    public function getLatestQuestions()
    {
        return $this->repository->getLatest();
    }

    public function getQuestionById($id)
    {
        return $this->repository->findById($id);
    }

    public function createQuestion(array $data)
    {
        $data['user_id'] = Auth::id();
        return $this->repository->create($data);
    }

    public function updateQuestion($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteQuestion($id)
    {
        $this->repository->delete($id);
    }

    public function upvoteQuestion($id)
    {
        $this->repository->upvote($id, Auth::id());
    }
}
