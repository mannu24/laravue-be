<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Questions\StoreQuestionRequest;
use App\Http\Requests\v1\User\Questions\UpdateQuestionRequest;
use App\Http\Resources\v1\AnswerResource;
use App\Http\Resources\v1\QuestionResource;
use App\Http\Traits\HttpResponse;
use App\Models\Notification;
use App\Services\AnswerService;
use App\Services\NotificationService;
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
        
        // Transform paginated questions using QuestionResource
        $transformedQuestions = $questions->getCollection()->map(function ($question) {
            return new QuestionResource($question);
        });
        
        // Create new paginator with transformed data
        $paginatedData = new LengthAwarePaginator(
            $transformedQuestions,
            $questions->total(),
            $questions->perPage(),
            $questions->currentPage(),
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

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
        try {
            $question = $this->service->createQuestion($validated);

            return $this->success(
                data: new QuestionResource($question),
                message: 'Question created successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to add Question!'
            );
        }
    }

    public function update(UpdateQuestionRequest $request, $id)
    {
        try {
            $validated = $request->validated();

            // Ensure the authenticated user owns the question
            $question = $this->service->getQuestionById($id);
            if ($question->user_id !== auth()->guard('api')->id()) {
                return $this->error(
                    message: 'You are not authorized to edit this question.',
                    code: 403
                );
            }

            $updatedQuestion = $this->service->updateQuestion($id, $validated);

            return $this->success(
                data: new QuestionResource($updatedQuestion),
                message: 'Question updated successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to update question: ' . $e->getMessage()
            );
        }
    }

    public function destroy($slug)
    {
        $this->service->deleteQuestion($slug);
        return $this->success(
            message: 'Question deleted successfully'
        );
    }

    public function toggleUpvote($id)
    {
        try {
            $question = $this->service->getQuestionById($id);
            $upvoted = $this->service->toggleUpvote($id);
            
            // Notify question owner (if not upvoting own question)
            if ($question->user_id !== auth()->guard('api')->id()) {
                $questionUrl = "/qna/{$question->slug}";
                NotificationService::create(
                    userId: $question->user_id,
                    type: Notification::TYPE_QUESTION_UPVOTED,
                    title: 'Your question was upvoted',
                    message: auth()->user()->name . ' upvoted your question',
                    subject: $question,
                    notifiableId: auth()->guard('api')->id(),
                    data: ['url' => $questionUrl, 'question_title' => $question->title],
                    sendEmail: true,
                    emailBlade: 'emails.notification',
                    emailSubject: 'Someone upvoted your question'
                );
            }
            
            return $this->success(
                data: [
                    'upvoted' => $upvoted,
                    'upvotes_count' => $question->upvotes()->count()
                ],
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
            $question = $this->service->getQuestionBySlug($slug);
            $wasLiked = $question->liked;
            $liked = $this->service->like_unlike($slug);
            
            // Refresh question to get updated likes count
            $question->refresh();
            $likesCount = $question->likes()->count();
            
            // Notify question owner when liked (if not liking own question)
            if ($liked && !$wasLiked && $question->user_id !== auth()->guard('api')->id()) {
                $questionUrl = "/qna/{$question->slug}";
                NotificationService::create(
                    userId: $question->user_id,
                    type: Notification::TYPE_QUESTION_LIKED,
                    title: 'Your question was liked',
                    message: auth()->user()->name . ' liked your question',
                    subject: $question,
                    notifiableId: auth()->guard('api')->id(),
                    data: ['url' => $questionUrl, 'question_title' => $question->title],
                    sendEmail: true,
                    emailBlade: 'emails.notification',
                    emailSubject: 'Someone liked your question'
                );
            }
            
            return response()->json([
                'status' => 'success', 
                'liked' => $liked,
                'likes_count' => $likesCount
            ]);
        } catch (Exception $e) {
            return $this->internalError(
                message: $e->getMessage()
            );
        }
    }
}
