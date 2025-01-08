<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        "title",
        "user_id",
        "slug",
        "meta_title",
        "meta_description",
        "meta_keywords",
        "description",
        "content",
        "image",
        "status",
        "views"
    ];
}
