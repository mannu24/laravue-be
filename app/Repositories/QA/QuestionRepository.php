<?php

declare(strict_types=1);

namespace App\Repositories\QA;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class QuestionRepository
{
    public function __construct(protected Question $model)
    {
    }

    /**
     * Create a new question.
     */
    public function create(array $data): Question
    {
        return $this->model->create($data);
    }

    /**
     * Find question with relations.
     */
    public function findWithRelations(int $id): ?Question
    {
        return $this->model->with([
            'user',
            'answers' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'answers.user',
            'tags',
        ])->find($id);
    }

    /**
     * Search questions.
     */
    public function search(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where('title', 'like', "%{$query}%")
            ->orWhere('body', 'like', "%{$query}%")
            ->orWhere('ai_generated_summary', 'like', "%{$query}%")
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Increment views for a question.
     */
    public function incrementViews(Question $question): Question
    {
        $question->increment('views');
        return $question->fresh();
    }
}

