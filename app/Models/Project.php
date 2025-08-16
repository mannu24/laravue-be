<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $project->slug = self::generateUniqueSlug($project->title);
            $project->user_id = Auth::id();
        });

        static::updating(function ($project) {
            if ($project->isDirty('title')) {
                $project->slug = self::generateUniqueSlug($project->title);
            }
        });
    }

    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $randomStr = Str::random(10);
        return "{$slug}-{$randomStr}";
    }
}
