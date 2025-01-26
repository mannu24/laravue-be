<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia ;
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

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getMediaUrlsAttribute() {
        return $this->getMedia('*')->map(function ($media) {
            return $media->getUrl();
        });
    }
}
