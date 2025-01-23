<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upvote extends Model
{
    protected $table = 'upvotes';

    protected $fillable = [
        'user_id',
        'record_id',
        'record_type',
    ];

    public function record()
    {
        return $this->morphTo();
    }
}
