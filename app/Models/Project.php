<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'project_type',
        'github_url',
        'demo_url',
        'is_sellable',
        'original_price',
        'selling_price',
        'views',
        'is_active'
    ];
}
