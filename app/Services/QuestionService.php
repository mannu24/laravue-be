<?php

namespace App\Services;

use App\Repositories\QuestionRepository;

class QuestionService
{
    protected QuestionRepository $repository;
    protected TagService $tagService;

    public function __construct(
        QuestionRepository $repository,
        TagService $tagService
    ) {
        $this->repository = $repository;
        $this->tagService = $tagService;
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

    public function getQuestionBySlug($slug)
    {
        return $this->repository->findBySlug($slug);
    }

    public function createQuestion(array $data)
    {
        $data['user_id'] = auth()->guard('api')->id();
        $question = $this->repository->create($data);
        $this->tagService->addTags(
            tags: $data['tags'],
            recordId: $question->id,
            recordType: 'questions',
            userId: $data['user_id']
        );
        return $question;
    }

    public function updateQuestion($id, array $data)
    {
        $data['user_id'] = auth()->guard('api')->id();
        $question = $this->repository->update($id, $data);
        // deleting old tags
        $this->tagService->deleteTags(
            recordId: $id,
        );
        // adding new tags
        $this->tagService->addTags(
            tags: $data['tags'],
            recordId: $id,
            recordType: 'questions',
            userId: $data['user_id']
        );
        return $question;
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
