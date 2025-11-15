<?php

namespace App\Repositories;

use App\Models\UserGitHubToken;
use Illuminate\Database\Eloquent\Model;

class UserGitHubTokenRepository
{
    protected UserGitHubToken $model;

    public function __construct(UserGitHubToken $model)
    {
        $this->model = $model;
    }

    /**
     * Get token for user.
     */
    public function getByUserId(int $userId): ?UserGitHubToken
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Check if user has valid token.
     */
    public function hasValidToken(int $userId): bool
    {
        $token = $this->getByUserId($userId);
        return $token && $token->isValid();
    }

    /**
     * Delete token for user.
     */
    public function deleteByUserId(int $userId): bool
    {
        return $this->model->where('user_id', $userId)->delete();
    }
}

