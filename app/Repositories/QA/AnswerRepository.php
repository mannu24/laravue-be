<?php

declare(strict_types=1);

namespace App\Repositories\QA;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Collection;

class AnswerRepository
{
    public function __construct(protected Answer $model)
    {
    }

    /**
     * Create a new answer.
     */
    public function create(array $data): Answer
    {
        return $this->model->create($data);
    }

    /**
     * Get answers for a question.
     */
    public function getAnswersForQuestion(int $questionId): Collection
    {
        return $this->model->where('question_id', $questionId)
            ->with('user')
            ->orderBy('is_verified', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Verify an answer.
     */
    public function verifyAnswer(int $answerId): Answer
    {
        $answer = $this->model->findOrFail($answerId);
        $answer->update(['is_verified' => true]);
        return $answer->fresh();
    }

    /**
     * Get AI-generated answers for a question.
     */
    public function getAiGeneratedAnswers(int $questionId): Collection
    {
        return $this->model->where('question_id', $questionId)
            ->where('is_ai_generated', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

