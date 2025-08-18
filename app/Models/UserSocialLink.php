<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSocialLink extends Model
{
    protected $fillable = [
        'user_id',
        'social_link_type_id',
        'username',
        'url',
        'position',
        'clicks',
        'is_visible'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialLinkType()
    {
        return $this->belongsTo(SocialLinkType::class);
    }
}
