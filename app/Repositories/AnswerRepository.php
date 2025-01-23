<?php

namespace App\Repositories;

use App\Models\Answer;
use App\Models\Question;

class AnswerRepository
{
    public function getAnswersByQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);

        return $question->answers()
            ->whereNull('parent_id')
            ->with(['user', 'replies'])
            ->paginate(10);
    }

    public function createAnswer(array $data, $questionId)
    {
        $question = Question::findOrFail($questionId);

        return $question->answers()->create([
            'content' => $data['content'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'parent_id' => null,
        ]);
    }

    public function getAnswerById($answerId)
    {
        return Answer::with(['user', 'question', 'replies'])->findOrFail($answerId);
    }

    public function updateAnswer(array $data, $answerId)
    {
        $answer = Answer::findOrFail($answerId);
        $answer->update($data);

        return $answer;
    }

    public function deleteAnswer($answerId)
    {
        $answer = Answer::findOrFail($answerId);
        $answer->delete();
    }

    public function getReplies($answerId)
    {
        $answer = Answer::findOrFail($answerId);

        return $answer->replies()->with(['user', 'replies'])->get();
    }

    public function createReply(array $data, $answerId)
    {
        $answer = Answer::findOrFail($answerId);

        return Answer::create([
            'content' => $data['content'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'question_id' => $answer->question_id,
            'parent_id' => $answer->id,
        ]);
    }
}
