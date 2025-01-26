<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Questions\StoreQuestionRequest;
use App\Http\Requests\v1\User\Questions\UpdateQuestionRequest;
use App\Http\Resources\v1\QuestionResource;
use App\Http\Traits\HttpResponse;
use App\Services\QuestionService;
use Exception;

class QuestionController extends Controller
{
    use HttpResponse;

    protected $service;

    public function __construct(QuestionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $questions = $this->service->getAllQuestions();
        return $this->success(
            data: QuestionResource::collection($questions),
            message: 'Questions fetched successfully',
        );
    }

    public function latest()
    {
        $questions = $this->service->getLatestQuestions();
        return $this->success(
            data: QuestionResource::collection($questions),
            message: 'Latest questions fetched successfully',
        );
    }

    public function show($id)
    {
        $question = $this->service->getQuestionById($id);
        return $this->success(
            data: new QuestionResource($question),
            message: 'Question fetched successfully'
        );
    }

    public function store(StoreQuestionRequest $request)
    {
        $validated = $request->validated();

        $question = $this->service->createQuestion($validated);

        return $this->success(
            data: new QuestionResource($question),
            message: 'Question created successfully'
        );
    }

    public function update(UpdateQuestionRequest $request, $id)
    {
        $validated = $request->validated();

        $question = $this->service->updateQuestion($id, $validated);
        return $this->success(
            data: new QuestionResource($question),
            message: 'Question updated successfully'
        );
    }

    public function destroy($id)
    {
        $this->service->deleteQuestion($id);
        return $this->success(
            message: 'Question deleted successfully'
        );
    }

    public function upvote($id)
    {
        try {
            $this->service->upvoteQuestion($id);
            return $this->success(
                message: 'Question upvoted successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: $e->getMessage()
            );
        }
    }
}
