<?php

namespace App\Services\GitHub;

use App\Models\UserGitHubToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GitHubOAuthService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $baseUrl = 'https://github.com';
    private string $apiUrl = 'https://api.github.com';

    public function __construct()
    {
        $this->clientId = config('services.github.client_id');
        $this->clientSecret = config('services.github.client_secret');
        $this->redirectUri = config('services.github.redirect');
    }

    /**
     * Get the GitHub OAuth authorization URL.
     */
    public function getAuthorizationUrl(string $state, bool $popup = false): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri . ($popup ? '?popup=1' : ''),
            'scope' => 'repo read:user user:email',
            'state' => $state,
            'response_type' => 'code',
        ];

        return $this->baseUrl . '/login/oauth/authorize?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function exchangeCodeForToken(string $code): array
    {
        try {
            $response = Http::asForm()->post($this->baseUrl . '/login/oauth/access_token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ]);

            if ($response->failed()) {
                Log::error('GitHub OAuth token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Failed to exchange authorization code for token');
            }

            $data = [];
            parse_str($response->body(), $data);

            if (!isset($data['access_token'])) {
                throw new Exception('Access token not received from GitHub');
            }

            return [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'token_type' => $data['token_type'] ?? 'bearer',
                'scope' => $data['scope'] ?? '',
                'expires_in' => isset($data['expires_in']) ? (int) $data['expires_in'] : null,
            ];
        } catch (Exception $e) {
            Log::error('GitHub OAuth error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get user information from GitHub.
     */
    public function getUserInfo(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get($this->apiUrl . '/user');

            if ($response->failed()) {
                throw new Exception('Failed to fetch user info from GitHub');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('GitHub API error - getUserInfo', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Store or update GitHub token for user.
     */
    public function storeToken(int $userId, array $tokenData, array $userInfo): UserGitHubToken
    {
        $expiresAt = null;
        if (isset($tokenData['expires_in']) && $tokenData['expires_in']) {
            $expiresAt = now()->addSeconds($tokenData['expires_in']);
        }

        $scopes = isset($tokenData['scope']) 
            ? explode(',', $tokenData['scope']) 
            : [];

        return UserGitHubToken::updateOrCreate(
            ['user_id' => $userId],
            [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => $expiresAt,
                'github_username' => $userInfo['login'] ?? null,
                'github_user_id' => (string) ($userInfo['id'] ?? null),
                'github_email' => $userInfo['email'] ?? null,
                'github_avatar_url' => $userInfo['avatar_url'] ?? null,
                'scopes' => $scopes,
            ]
        );
    }

    /**
     * Revoke GitHub token.
     */
    public function revokeToken(string $accessToken): bool
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->delete($this->apiUrl . '/applications/' . $this->clientId . '/token', [
                    'access_token' => $accessToken,
                ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('GitHub token revocation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

