<?php

declare(strict_types=1);

namespace App\Services\QA;

use App\Enums\XpEventType;
use App\Models\Question;
use App\Models\Answer;
use App\Repositories\QA\AnswerRepository;
use App\Services\Achievements\AchievementsPipeline;
use App\Services\Gamification\XpService;
use Illuminate\Support\Collection;

class AnswerService
{
    public function __construct(
        protected AnswerRepository $answerRepository,
        protected ?XpService $xpService = null,
        protected ?AchievementsPipeline $achievements = null
    ) {
    }

    /**
     * Store AI-generated answer.
     */
    public function storeAiAnswer(Question $question, string $text): Answer
    {
        return $this->answerRepository->create([
            'question_id' => $question->id,
            'user_id' => null, // AI answers have no user
            'body' => $text,
            'is_ai_generated' => true,
            'is_verified' => false,
        ]);
    }

    /**
     * Create user answer.
     */
    public function createUserAnswer(array $data): Answer
    {
        $answer = $this->answerRepository->create($data);

        // Award XP for providing an answer
        if ($this->xpService && $answer->user) {
            $this->xpService->awardXpForEvent($answer->user, XpEventType::ANSWER_CREATED);
        }

        return $answer;
    }

    /**
     * Verify an answer.
     */
    public function verifyAnswer(int $answerId): Answer
    {
        $answer = $this->answerRepository->verifyAnswer($answerId);

        // Award XP to answer author if verified
        if ($this->xpService && $answer->user) {
            $this->xpService->awardXpForEvent($answer->user, XpEventType::ANSWER_VERIFIED);
        }

        // Trigger achievement event
        if ($this->achievements && $answer->user) {
            $this->achievements->triggerAnswerVerified($answer->user, $answer);
        }

        return $answer;
    }

    /**
     * Get answers for a question.
     */
    public function getAnswersForQuestion(int $questionId): Collection
    {
        return $this->answerRepository->getAnswersForQuestion($questionId);
    }
}

