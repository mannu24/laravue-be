<?php

namespace App\Services;

use App\Enums\XpEventType;
use App\Models\User;
use App\Repositories\QuestionRepository;
use App\Services\Gamification\XpService;
use App\Services\Gamification\GamificationService;
use App\Services\Gamification\TaskService;

class QuestionService
{
    protected QuestionRepository $repository;
    protected TagService $tagService;
    protected ?XpService $xpService;
    protected ?GamificationService $gamificationService;
    protected ?TaskService $taskService;

    public function __construct(
        QuestionRepository $repository,
        TagService $tagService,
        ?XpService $xpService = null,
        ?GamificationService $gamificationService = null,
        ?TaskService $taskService = null
    ) {
        $this->repository = $repository;
        $this->tagService = $tagService;
        $this->xpService = $xpService;
        $this->gamificationService = $gamificationService;
        $this->taskService = $taskService;
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
        
        // Award XP and process all gamification effects (tasks, badges, level ups)
        // BUT: If there's a pending "Ask 1 Question" task, don't award XP here
        // The task completion will award XP instead to avoid double rewards
        if ($question->user_id) {
            $user = User::find($question->user_id);
            if ($user) {
                // Check if user has a pending "Ask 1 Question" task for today
                $hasPendingTask = \App\Models\UserTask::where('user_id', $user->id)
                    ->whereHas('task', function ($query) {
                        $query->where('title', 'Ask 1 Question')
                              ->where('is_active', true);
                    })
                    ->where('status', 'pending')
                    ->whereDate('assigned_at', today())
                    ->exists();
                
                if ($hasPendingTask && $this->taskService) {
                    try {
                        $this->taskService->completeByTitle('Ask 1 Question', $user);
                    } catch (\Exception $e) {
                        logger()->debug('[QuestionService] Task auto-completion failed', [
                            'user_id' => $user->id,
                            'question_id' => $question->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    // Only award XP for QUESTION_CREATED if there's no pending task
                    // If there's a pending task, the task completion will award XP instead
                    if ($this->gamificationService) {
                        $this->gamificationService->awardXpAndProcess(
                            $user,
                            XpEventType::QUESTION_CREATED,
                            ['question_id' => $question->id]
                        );
                    } elseif ($this->xpService) {
                        // Fallback to just XP if unified service not available
                        $this->xpService->awardXpForEvent($user, XpEventType::QUESTION_CREATED);
                    }
                }
            }
        }
        
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

    public function toggleUpvote($id)
    {
        $this->repository->toggleUpvote($id);
        return $this->repository->findById($id)->upvoted;
    }

    public function like_unlike($slug)
    {
        return $this->repository->like_unlike($slug);
    }
}
