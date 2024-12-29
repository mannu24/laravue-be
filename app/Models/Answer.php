<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    protected $fillable = [
        'question_id',
        'user_id',
        'content',
        'content_html',
        'is_accepted',
        'score',
        'comment_count',
        'last_activity_date',
        'source',
        'source_url',
        'source_answer_id'
    ];
}
