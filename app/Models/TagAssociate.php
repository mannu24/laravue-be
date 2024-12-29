<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagAssociate extends Model
{
    protected $table = 'tag_associates';

    protected $fillable = [
        'tag_id',
        'record_id',
        'record_type'
    ];
}