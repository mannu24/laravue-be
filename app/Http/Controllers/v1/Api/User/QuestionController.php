<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Questions\StoreQuestionRequest;
use App\Http\Requests\v1\User\Questions\UpdateQuestionRequest;
use App\Http\Resources\v1\AnswerResource;
use App\Http\Resources\v1\QuestionResource;
use App\Http\Traits\HttpResponse;
use App\Services\AnswerService;
use App\Services\QuestionService;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class QuestionController extends Controller
{
    use HttpResponse;

    protected QuestionService $service;
    protected AnswerService $answerService;

    public function __construct(QuestionService $service, AnswerService $answerService)
    {
        $this->service = $service;
        $this->answerService = $answerService;
    }

    public function index()
    {
        $questions = $this->service->getAllQuestions();
        // $questions = QuestionResource::collection($questions) ;

        $page = $_GET['page'];
        $perPage = 2;
        $paginatedData = new LengthAwarePaginator($questions->forPage($page, $perPage)->values(), $questions->count(), $perPage);

        return response()->json(['status' => 'success', 'records' => $paginatedData]);
    }

    public function latest()
    {
        $questions = $this->service->getLatestQuestions();
        return $this->success(
            data: QuestionResource::collection($questions),
            message: 'Latest questions fetched successfully',
        );
    }

    public function show($slug)
    {
        $question = $this->service->getQuestionBySlug($slug);
        $answers = $this->answerService->getAnswersByQuestion($question->id);
        return $this->success(
            data: [
                'question' => new QuestionResource($question),
                'answers' => AnswerResource::collection($answers)
            ],
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

    public function destroy($slug)
    {
        $this->service->deleteQuestion($slug);
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

    public function like_unlike($slug)
    {
        try {
            $liked = $this->service->like_unlike($slug);
            return response()->json(['status' => 'success', 'liked' => $liked]);
        } catch (Exception $e) {
            return $this->internalError(
                message: $e->getMessage()
            );
        }
    }
}
