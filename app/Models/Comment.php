<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    protected $fillable = ['record_id','record_type','user_id','content'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['posted_at', 'owner', 'liked'];
    
    public function getOwnerAttribute() {
        return $this->user_id === auth()->guard('api')->id();
    }
    
    public function getLikedAttribute() {
        return !auth()->guard('api')->check() ? false : $this->likes()->where('user_id', auth()->guard('api')->id())->exists();
    }
    
    public function getPostedAtAttribute() {
        return Carbon::parse($this->created_at)->diffForHumans() ;
    }
    
    public function likes() {
        return $this->hasMany(Like::class, 'record_id', 'id')->where('likes.record_type', 'comment');
    }

    public function toggleLike() {
        if($this->likes()->where('user_id', auth()->guard('api')->id())->exists()) {
            Like::where('user_id', auth()->guard('api')->id())->where('record_id', $this->id)->where('record_type', 'comment')->delete();
            return false; // Return false when unliked
        } else {
            Like::create([
                'user_id' => auth()->guard('api')->id(),
                'record_id' => $this->id,
                'record_type' => 'comment'
            ]);
            return true; // Return true when liked
        }
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot method to track activities
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($comment) {
            $comment->load('user');
            \App\Models\Activity::createActivity(
                $comment->user_id,
                \App\Models\Activity::TYPE_COMMENT_CREATED,
                $comment,
                ($comment->user->name ?? 'User') . ' commented on a ' . $comment->record_type,
                [
                    'record_type' => $comment->record_type,
                    'record_id' => $comment->record_id,
                ]
            );
        });
    }
}