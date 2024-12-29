<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeveloperTechnology extends Model
{
    protected $table = 'developer_technologies';

    protected $fillable = [
        'user_id',
        'tech_id'
    ];
}
