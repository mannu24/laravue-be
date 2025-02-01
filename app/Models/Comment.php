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
        } else {
            Like::create([
                'user_id' => auth()->guard('api')->id(),
                'record_id' => $this->id,
                'record_type' => 'comment'
            ]);
        }
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}