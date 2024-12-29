<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeveloperProfile extends Model
{
    protected $table = 'developer_profiles';

    protected $fillable = [
        'user_id',
        'username',
        'bio',
        'github_username',
        'linkedin_username',
        'website',
        'avatar',
        'reputation_points',
        'credits_balance'
    ];
}
