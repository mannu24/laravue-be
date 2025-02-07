<?php

namespace App\Services;

use App\Repositories\QuestionRepository;

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
        $data['user_id'] = auth()->guard('api')->id();
        return $this->repository->create($data);
    }

    public function updateQuestion($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteQuestion($slug)
    {
        $this->repository->delete($slug);
    }

    public function upvoteQuestion($id)
    {
        $this->repository->upvote($id, auth()->guard('api')->id());
    }

    public function like_unlike($slug)
    {
        return $this->repository->like_unlike($slug);
    }
}
