<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mention extends Model
{
    protected $table = 'mentions';

    protected $fillable = [
        'post_id',
        'user_id',
        'position'
    ];
}
