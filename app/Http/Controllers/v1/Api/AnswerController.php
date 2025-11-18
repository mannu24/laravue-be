<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QA\CreateAnswerRequest;
use App\Http\Requests\QA\VerifyAnswerRequest;
use App\Http\Resources\v1\AnswerResource;
use App\Http\Traits\HttpResponse;
use App\Models\Question;
use App\Services\QA\AnswerService;
use Illuminate\Http\JsonResponse;

class AnswerController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected AnswerService $answerService
    ) {
    }

    /**
     * Create a user-submitted answer.
     */
    public function store(CreateAnswerRequest $request): JsonResponse
    {
        $answer = $this->answerService->createUserAnswer($request->validated());
        
        return $this->success(
            data: new AnswerResource($answer->load('user')),
            message: 'Answer created successfully'
        );
    }

    /**
     * Verify an answer.
     */
    public function verify(VerifyAnswerRequest $request): JsonResponse
    {
        $answer = $this->answerService->verifyAnswer($request->answer_id);
        
        return $this->success(
            data: new AnswerResource($answer->load('user')),
            message: 'Answer verified successfully'
        );
    }

    /**
     * Get all answers for a question.
     */
    public function questionAnswers(Question $question): JsonResponse
    {
        $answers = $this->answerService->getAnswersForQuestion($question->id);
        
        return $this->success(
            data: AnswerResource::collection($answers),
            message: 'Answers retrieved successfully'
        );
    }
}
