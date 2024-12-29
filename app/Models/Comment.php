<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    protected $fillable = [
        'record_id',
        'record_type',
        'user_id',
        'content'
    ];

}