<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLinkType extends Model
{
    protected $fillable = ['name', 'icon', 'base_url', 'is_active'];
}
