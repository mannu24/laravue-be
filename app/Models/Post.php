<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'post_code',
        'title',
        'meta_content',
        'content',
        'views',
        'is_ai_generated',
        'is_blocked'
    ];
}
