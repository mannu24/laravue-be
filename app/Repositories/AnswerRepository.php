<?php

namespace App\Repositories;

use App\Models\Answer;
use App\Models\Question;

class AnswerRepository
{

    public function __construct(protected Answer $model, protected Question $question) {}

    public function getAnswersByQuestion($questionId)
    {
        $question = $this->question->findOrFail($questionId);

        return $question->answers()
            ->whereNull('parent_id')
            ->with(['user', 'replies'])
            ->withCount('upvotes')
            ->latest()
            ->paginate(10);
    }

    public function createAnswer(array $data, $questionId)
    {
        $question = $this->question->findOrFail($questionId);

        return $question->answers()->create([
            'content' => $data['content'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'parent_id' => null,
        ]);
    }

    public function getAnswerById($answerId)
    {
        return $this->model->with(['user', 'question', 'replies'])->findOrFail($answerId);
    }

    public function updateAnswer(array $data, $answerId)
    {
        $answer = $this->model->findOrFail($answerId);
        $answer->update($data);

        return $answer;
    }

    public function upvote($id, $userId)
    {
        $question = $this->model->findOrFail($id);

        if ($question->user_id === Auth()->id()) {
            throw new \Exception("You cannot upvote your own question.");
        }

        if ($question->upvotes()->where('user_id', $userId)->exists()) {
            throw new \Exception("You have already upvoted this question.");
        }

        $question->upvotes()->create(['user_id' => $userId]);
    }

    public function deleteAnswer($answerId)
    {
        $answer = $this->model->findOrFail($answerId);
        $answer->delete();
    }

    public function getReplies($answerId)
    {
        $answer = $this->model->findOrFail($answerId);

        return $answer->replies()->with(['user', 'replies'])->get();
    }

    public function createReply(array $data, $answerId)
    {
        $answer = $this->model->findOrFail($answerId);

        return $this->model->create([
            'content' => $data['content'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'question_id' => $answer->question_id,
            'parent_id' => $answer->id,
        ]);
    }
}
