<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Answers\StoreAnswerReplyRequest;
use App\Http\Requests\v1\User\Answers\StoreAnswerRequest;
use App\Http\Requests\v1\User\Answers\UpdateAnswerRequest;
use App\Http\Resources\v1\AnswerResource;
use App\Http\Traits\HttpResponse;
use App\Models\Notification;
use App\Services\AnswerService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    use HttpResponse;

    private AnswerService $service;

    public function __construct(AnswerService $service)
    {
        $this->service = $service;
    }

    public function index($questionId)
    {
        $answers = $this->service->getAnswersByQuestion($questionId);

        return $this->success(
            data: AnswerResource::collection($answers),
            message: 'Answers fetched successfully'
        );
    }

    public function store(StoreAnswerRequest $request, $questionId)
    {
        $validated = $request->validated();
        $answer = $this->service->createAnswer($validated, $questionId);
        $answer->load('question');

        // Notify question owner (if not answering own question)
        if ($answer->question->user_id !== auth()->guard('api')->id()) {
            $questionUrl = "/qna/{$answer->question->slug}";
            NotificationService::create(
                userId: $answer->question->user_id,
                type: Notification::TYPE_QUESTION_ANSWERED,
                title: 'New answer on your question',
                message: auth()->user()->name . ' answered your question',
                subject: $answer,
                notifiableId: auth()->guard('api')->id(),
                data: ['url' => $questionUrl, 'question_title' => $answer->question->title],
                sendEmail: true,
                emailBlade: 'emails.notification',
                emailSubject: 'You have a new answer on your question'
            );
        }

        return $this->success(
            data: new AnswerResource($answer),
            message: 'Answer created successfully'
        );
    }

    public function upvote($id)
    {
        try {
            $answer = $this->service->getAnswer($id);
            $this->service->upvoteAnswer($id);
            
            // Notify answer owner (if not upvoting own answer)
            if ($answer->user_id !== auth()->guard('api')->id()) {
                $answer->load('question');
                $questionUrl = $answer->question ? "/qna/{$answer->question->slug}" : '#';
                NotificationService::create(
                    userId: $answer->user_id,
                    type: Notification::TYPE_ANSWER_UPVOTED,
                    title: 'Your answer was upvoted',
                    message: auth()->user()->name . ' upvoted your answer',
                    subject: $answer,
                    notifiableId: auth()->guard('api')->id(),
                    data: ['url' => $questionUrl],
                    sendEmail: true,
                    emailBlade: 'emails.notification',
                    emailSubject: 'Someone upvoted your answer'
                );
            }
            
            return $this->success(
                message: 'Answer upvoted successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: $e->getMessage()
            );
        }
    }

    public function show($answerId)
    {
        $answer = $this->service->getAnswer($answerId);

        return $this->success(
            data: new AnswerResource($answer),
            message: 'Answer fetched successfully'
        );
    }

    public function update(UpdateAnswerRequest $request, $answerId)
    {
        try {
            $validated = $request->validated();
            $answer = $this->service->updateAnswer($validated, $answerId);

            return $this->success(
                data: new AnswerResource($answer),
                message: 'Answer updated successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: $e->getMessage()
            );
        }
    }

    public function destroy($answerId)
    {
        try {
            $this->service->deleteAnswer($answerId);

            return $this->success(
                message: 'Answer deleted successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: $e->getMessage()
            );
        }
    }

    public function getReplies($answerId)
    {
        $replies = $this->service->getReplies($answerId);

        return $this->success(
            data: AnswerResource::collection($replies),
            message: 'Replies fetched successfully'
        );
    }

    public function storeReply(StoreAnswerReplyRequest $request, $answerId)
    {
        $validated = $request->validated();
        $reply = $this->service->createReply($validated, $answerId);
        $reply->load(['parent.question', 'parent.user']);

        // Notify answer owner (if not replying to own answer)
        if ($reply->parent->user_id !== auth()->guard('api')->id()) {
            $questionUrl = $reply->parent->question ? "/qna/{$reply->parent->question->slug}" : '#';
            NotificationService::create(
                userId: $reply->parent->user_id,
                type: Notification::TYPE_ANSWER_REPLIED,
                title: 'New reply to your answer',
                message: auth()->user()->name . ' replied to your answer',
                subject: $reply,
                notifiableId: auth()->guard('api')->id(),
                data: ['url' => $questionUrl],
                sendEmail: true,
                emailBlade: 'emails.notification',
                emailSubject: 'You have a new reply to your answer'
            );
        }

        // Also notify question owner if different from answer owner
        if ($reply->parent->question && 
            $reply->parent->question->user_id !== auth()->guard('api')->id() &&
            $reply->parent->question->user_id !== $reply->parent->user_id) {
            $questionUrl = "/qna/{$reply->parent->question->slug}";
            NotificationService::create(
                userId: $reply->parent->question->user_id,
                type: Notification::TYPE_ANSWER_REPLIED,
                title: 'New reply on your question',
                message: auth()->user()->name . ' replied to an answer on your question',
                subject: $reply,
                notifiableId: auth()->guard('api')->id(),
                data: ['url' => $questionUrl],
                sendEmail: true,
                emailBlade: 'emails.notification',
                emailSubject: 'You have a new reply on your question'
            );
        }

        return response()->json(new AnswerResource($reply), 201);
    }
}
