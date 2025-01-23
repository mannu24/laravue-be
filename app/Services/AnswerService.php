<?php

namespace App\Services;

use App\Repositories\AnswerRepository;

class AnswerService
{
    private AnswerRepository $repository;

    public function __construct(AnswerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAnswersByQuestion($questionId)
    {
        return $this->repository->getAnswersByQuestion($questionId);
    }

    public function createAnswer(array $data, $questionId)
    {
        return $this->repository->createAnswer($data, $questionId);
    }

    public function getAnswer($answerId)
    {
        return $this->repository->getAnswerById($answerId);
    }

    public function updateAnswer(array $data, $answerId)
    {
        return $this->repository->updateAnswer($data, $answerId);
    }

    public function deleteAnswer($answerId)
    {
        return $this->repository->deleteAnswer($answerId);
    }

    public function getReplies($answerId)
    {
        return $this->repository->getReplies($answerId);
    }

    public function createReply(array $data, $answerId)
    {
        return $this->repository->createReply($data, $answerId);
    }
}
