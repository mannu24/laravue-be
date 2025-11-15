<?php

namespace App\Services\GitHub;

use App\Models\UserGitHubToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class GitHubApiService
{
    private string $apiUrl = 'https://api.github.com';

    /**
     * Get authenticated HTTP client with token.
     */
    private function getClient(UserGitHubToken $token)
    {
        if ($token->isExpired()) {
            throw new Exception('GitHub token has expired. Please reconnect your GitHub account.');
        }

        return Http::withToken($token->access_token)
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => config('app.name'),
            ]);
    }

    /**
     * Get user's repositories.
     */
    public function getRepositories(UserGitHubToken $token, array $options = []): array
    {
        $cacheKey = "github_repos_{$token->user_id}_" . md5(json_encode($options));
        
        return Cache::remember($cacheKey, 300, function () use ($token, $options) {
            try {
                $params = [
                    'type' => $options['type'] ?? 'all', // all, owner, member
                    'sort' => $options['sort'] ?? 'updated', // created, updated, pushed, full_name
                    'direction' => $options['direction'] ?? 'desc',
                    'per_page' => $options['per_page'] ?? 100,
                    'page' => $options['page'] ?? 1,
                ];

                // Filter out forks if requested
                if (isset($options['exclude_forks']) && $options['exclude_forks']) {
                    $params['type'] = 'owner';
                }

                $response = $this->getClient($token)
                    ->get($this->apiUrl . '/user/repos', $params);

                if ($response->failed()) {
                    Log::error('GitHub API error - getRepositories', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    throw new Exception('Failed to fetch repositories from GitHub');
                }

                $repos = $response->json();

                // Filter out forks if exclude_forks is true
                if (isset($options['exclude_forks']) && $options['exclude_forks']) {
                    $repos = array_filter($repos, fn($repo) => !$repo['fork']);
                }

                return array_values($repos);
            } catch (Exception $e) {
                Log::error('GitHub API error', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Get single repository details.
     */
    public function getRepository(UserGitHubToken $token, string $owner, string $repo): array
    {
        $cacheKey = "github_repo_{$owner}_{$repo}";
        
        return Cache::remember($cacheKey, 600, function () use ($token, $owner, $repo) {
            try {
                $response = $this->getClient($token)
                    ->get($this->apiUrl . "/repos/{$owner}/{$repo}");

                if ($response->failed()) {
                    throw new Exception("Failed to fetch repository: {$owner}/{$repo}");
                }

                return $response->json();
            } catch (Exception $e) {
                Log::error('GitHub API error - getRepository', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Get repository README content.
     */
    public function getReadme(UserGitHubToken $token, string $owner, string $repo, string $branch = 'main'): ?string
    {
        try {
            // Try main branch first
            $response = $this->getClient($token)
                ->get($this->apiUrl . "/repos/{$owner}/{$repo}/readme", [
                    'ref' => $branch,
                ]);

            if ($response->failed()) {
                // Try master branch
                $response = $this->getClient($token)
                    ->get($this->apiUrl . "/repos/{$owner}/{$repo}/readme", [
                        'ref' => 'master',
                    ]);
            }

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            
            // Decode base64 content
            if (isset($data['content'])) {
                return base64_decode(str_replace(["\n", "\r"], '', $data['content']));
            }

            return null;
        } catch (Exception $e) {
            Log::error('GitHub API error - getReadme', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get repository languages.
     */
    public function getLanguages(UserGitHubToken $token, string $owner, string $repo): array
    {
        try {
            $response = $this->getClient($token)
                ->get($this->apiUrl . "/repos/{$owner}/{$repo}/languages");

            if ($response->failed()) {
                return [];
            }

            return array_keys($response->json());
        } catch (Exception $e) {
            Log::error('GitHub API error - getLanguages', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get repository topics.
     */
    public function getTopics(UserGitHubToken $token, string $owner, string $repo): array
    {
        try {
            $response = $this->getClient($token)
                ->withHeaders(['Accept' => 'application/vnd.github.mercy-preview+json'])
                ->get($this->apiUrl . "/repos/{$owner}/{$repo}/topics");

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            return $data['names'] ?? [];
        } catch (Exception $e) {
            Log::error('GitHub API error - getTopics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Check if repository exists and user has access.
     */
    public function checkRepositoryAccess(UserGitHubToken $token, string $owner, string $repo): bool
    {
        try {
            $response = $this->getClient($token)
                ->get($this->apiUrl . "/repos/{$owner}/{$repo}");

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}

