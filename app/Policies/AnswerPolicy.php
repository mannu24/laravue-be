<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnswerPolicy
{
    public function update(User $user, Answer $answer)
    {
        return $user->id === $answer->user_id;
    }

    public function delete(User $user, Answer $answer)
    {
        return $user->id === $answer->user_id;
    }
}
