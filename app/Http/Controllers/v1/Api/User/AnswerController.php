<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Answers\StoreAnswerReplyRequest;
use App\Http\Requests\v1\User\Answers\StoreAnswerRequest;
use App\Http\Requests\v1\User\Answers\UpdateAnswerRequest;
use App\Http\Resources\v1\AnswerResource;
use App\Http\Traits\HttpResponse;
use App\Services\AnswerService;
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

        return $this->success(
            data: new AnswerResource($answer),
            message: 'Answer created successfully'
        );
    }

    public function upvote($id)
    {
        try {
            $this->service->upvoteAnswer($id);
            return $this->success(
                message: 'Question upvoted successfully'
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

        return response()->json(new AnswerResource($reply), 201);
    }
}
