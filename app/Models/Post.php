<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia ;
    protected $table = 'posts';
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['posted_at', 'owner', 'liked', 'media_urls'];

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

    public function getOwnerAttribute() {
        return $this->user_id === auth()->guard('api')->id();
    }
    
    public function getLikedAttribute() {
        return !auth()->guard('api')->check() ? false : $this->likes()->where('user_id', auth()->guard('api')->id())->exists();
    }
    
    public function getPostedAtAttribute() {
        return Carbon::parse($this->created_at)->diffForHumans() ;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function likes() {
        return $this->hasMany(Like::class, 'record_id', 'id')->where('likes.record_type', 'post');
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'record_id', 'id')->where('comments.record_type', 'post');
    }

    public function auth_comment() {
        return $this->comments()->where('user_id', auth()->guard('api')->id())->exists();
    }

    public function toggleLike() {
        if($this->likes()->where('user_id', auth()->guard('api')->id())->exists()) {
            Like::where('user_id', auth()->guard('api')->id())->where('record_id', $this->id)->where('record_type', 'post')->delete();
        } else {
            Like::create([
                'user_id' => auth()->guard('api')->id(),
                'record_id' => $this->id,
                'record_type' => 'post'
            ]);
        }
    }

    public function getMediaUrlsAttribute() {
        return $this->getMedia('*')->map(function ($media) {
            return $media->getUrl();
        });
    }
}
