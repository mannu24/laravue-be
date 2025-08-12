<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSocialLink;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocialLinkPolicy
{
    use HandlesAuthorization;

    public function update(User $user, UserSocialLink $socialLink)
    {
        return $user->id === $socialLink->user_id;
    }

    public function delete(User $user, UserSocialLink $socialLink)
    {
        return $user->id === $socialLink->user_id;
    }
}
