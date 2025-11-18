<?php

declare(strict_types=1);

namespace App\Services\QA;

use App\Enums\XpEventType;
use App\Models\Question;
use App\Repositories\QA\QuestionRepository;
use App\Repositories\QA\AnswerRepository;
use App\Services\Gamification\XpService;
use Illuminate\Pagination\LengthAwarePaginator;

class QuestionService
{
    public function __construct(
        protected QuestionRepository $questionRepository,
        protected AnswerRepository $answerRepository,
        protected ?XpService $xpService = null
    ) {
    }

    /**
     * Create a question.
     */
    public function createQuestion(array $data): Question
    {
        $question = $this->questionRepository->create($data);

        // Award XP for asking a question
        if ($this->xpService && $question->user) {
            $this->xpService->awardXpForEvent($question->user, XpEventType::QUESTION_CREATED);
        }

        return $question;
    }

    /**
     * Create question with AI-generated answer.
     */
    public function createWithAiAnswer(array $data): Question
    {
        // Create question
        $question = $this->createQuestion($data);

        // Generate AI answer (placeholder - replace with actual AI service call)
        $aiAnswerText = $this->generateAiAnswer($question->title, $question->body ?? '');

        // Store AI answer
        $this->answerRepository->create([
            'question_id' => $question->id,
            'user_id' => null, // AI answers have no user
            'body' => $aiAnswerText,
            'is_ai_generated' => true,
            'is_verified' => false,
        ]);

        // Award XP for asking a question
        if ($this->xpService && $question->user) {
            $this->xpService->awardXpForEvent($question->user, XpEventType::QUESTION_CREATED);
        }

        return $question->fresh(['answers']);
    }

    /**
     * Get question with answers.
     */
    public function getQuestionWithAnswers(int $id): ?Question
    {
        $question = $this->questionRepository->findWithRelations($id);

        if ($question) {
            // Increment views
            $this->questionRepository->incrementViews($question);
        }

        return $question;
    }

    /**
     * Search questions.
     */
    public function searchQuestions(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->questionRepository->search($query, $perPage);
    }

    /**
     * Generate AI answer (placeholder - replace with actual AI service).
     */
    private function generateAiAnswer(string $title, string $body): string
    {
        // TODO: Replace with actual AI service call
        // Example: return $this->aiService->generateAnswer($title, $body);
        
        return "This is a placeholder AI-generated answer for: {$title}. " .
               "In a real implementation, this would call an AI service to generate a contextual answer.";
    }
}

