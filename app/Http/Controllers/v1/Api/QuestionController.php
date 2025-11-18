<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QA\CreateQuestionRequest;
use App\Http\Requests\QA\SearchQuestionsRequest;
use App\Http\Resources\v1\QuestionResource;
use App\Http\Traits\HttpResponse;
use App\Models\Question;
use App\Services\QA\QuestionService;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected QuestionService $questionService
    ) {
    }

    /**
     * Create a new question.
     */
    public function store(CreateQuestionRequest $request): JsonResponse
    {
        $question = $this->questionService->createQuestion($request->validated());
        
        return $this->success(
            data: new QuestionResource($question->load(['user', 'answers'])),
            message: 'Question created successfully'
        );
    }

    /**
     * Show question with answers.
     */
    public function show(Question $question): JsonResponse
    {
        $question = $this->questionService->getQuestionWithAnswers($question->id);
        
        if (!$question) {
            abort(404, 'Question not found');
        }
        
        return $this->success(
            data: new QuestionResource($question),
            message: 'Question retrieved successfully'
        );
    }

    /**
     * Search questions.
     */
    public function search(SearchQuestionsRequest $request): JsonResponse
    {
        $questions = $this->questionService->searchQuestions(
            $request->input('query'),
            $request->input('per_page', 20)
        );
        
        return $this->success(
            data: QuestionResource::collection($questions),
            message: 'Questions retrieved successfully'
        );
    }
}
