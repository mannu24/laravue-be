<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'content_html',
        'is_solved',
        'score',
        'view_count',
        'last_activity_date',
        'source',
        'source_url',
        'source_question_id',
        'is_closed',
        'closed_reason'
    ];
}
