<?php

namespace App\Http\Controllers\v1\Api\GitHub;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\GitHub\ImportRepositoryRequest;
use App\Repositories\UserGitHubTokenRepository;
use App\Repositories\GitHubImportRepository;
use App\Services\GitHub\GitHubOAuthService;
use App\Services\GitHub\GitHubApiService;
use App\Services\GitHub\GitHubImportService;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class GitHubController extends Controller
{
    public function __construct(
        private GitHubOAuthService $oauthService,
        private GitHubApiService $apiService,
        private GitHubImportService $importService,
        private UserGitHubTokenRepository $tokenRepository,
        private GitHubImportRepository $importRepository
    ) {}

    /**
     * Initiate GitHub OAuth flow.
     */
    public function authorize(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated',
                ], 401);
            }

            $state = Str::random(40);
            session([
                'github_oauth_state' => $state,
                'github_oauth_user_id' => $user->id, // Store user ID for callback
            ]);
            
            $popup = $request->boolean('popup', true);
            $url = $this->oauthService->getAuthorizationUrl($state, $popup);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'authorization_url' => $url,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('GitHub OAuth initiation failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate GitHub authorization',
            ], 500);
        }
    }

    /**
     * Handle GitHub OAuth callback.
     */
    public function callback(Request $request)
    {
        try {
            // Verify state
            $state = $request->query('state');
            $sessionState = session('github_oauth_state');
            
            if (!$state || $state !== $sessionState) {
                if ($request->has('popup')) {
                    return response()->view('github.callback', [
                        'status' => 'error',
                        'message' => 'Invalid state parameter',
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid state parameter',
                ], 400);
            }

            // Check for error from GitHub
            if ($request->has('error')) {
                if ($request->has('popup')) {
                    return response()->view('github.callback', [
                        'status' => 'error',
                        'message' => $request->query('error_description', 'Authorization was denied'),
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => $request->query('error_description', 'Authorization was denied'),
                ], 400);
            }

            $code = $request->query('code');
            if (!$code) {
                if ($request->has('popup')) {
                    return response()->view('github.callback', [
                        'status' => 'error',
                        'message' => 'Authorization code not provided',
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authorization code not provided',
                ], 400);
            }

            // Get user ID from session (stored during authorize)
            $userId = session('github_oauth_user_id');
            if (!$userId) {
                if ($request->has('popup')) {
                    return response()->view('github.callback', [
                        'status' => 'error',
                        'message' => 'Session expired. Please try again.',
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired. Please try again.',
                ], 400);
            }

            // Exchange code for token
            $tokenData = $this->oauthService->exchangeCodeForToken($code);
            
            // Get user info
            $userInfo = $this->oauthService->getUserInfo($tokenData['access_token']);
            
            // Store token
            $token = $this->oauthService->storeToken(
                $userId,
                $tokenData,
                $userInfo
            );

            // Clear state and user ID from session
            session()->forget(['github_oauth_state', 'github_oauth_user_id']);

            // If this is a popup, return HTML that closes the popup
            if ($request->has('popup')) {
                return response()->view('github.callback', [
                    'status' => 'success',
                    'github_username' => $token->github_username,
                ]);
            }
            
            // Redirect to frontend with success
            $frontendUrl = config('app.frontend_url', config('app.url'));
            $redirectUrl = $frontendUrl . '/projects?github_connected=1';
            
            return redirect($redirectUrl);
        } catch (Exception $e) {
            Log::error('GitHub OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clear session on error
            session()->forget(['github_oauth_state', 'github_oauth_user_id']);

            if ($request->has('popup')) {
                return response()->view('github.callback', [
                    'status' => 'error',
                    'message' => 'Failed to connect GitHub account: ' . $e->getMessage(),
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect GitHub account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's GitHub repositories.
     */
    public function getRepositories(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $token = $this->tokenRepository->getByUserId($user->id);

            if (!$token || !$token->isValid()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GitHub account not connected. Please connect your GitHub account first.',
                ], 401);
            }

            $options = [
                'type' => $request->query('type', 'all'),
                'sort' => $request->query('sort', 'updated'),
                'direction' => $request->query('direction', 'desc'),
                'per_page' => $request->query('per_page', 100),
                'page' => $request->query('page', 1),
                'exclude_forks' => $request->boolean('exclude_forks', true),
            ];

            $repositories = $this->apiService->getRepositories($token, $options);
            
            // Get already imported repos
            $importedRepos = $this->importRepository->getImportedRepos($user->id);

            // Mark which repos are already imported
            $repositories = array_map(function ($repo) use ($importedRepos) {
                $fullName = $repo['full_name'];
                $repo['is_imported'] = in_array($fullName, $importedRepos);
                return $repo;
            }, $repositories);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'repositories' => $repositories,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch GitHub repositories', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch repositories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single repository details with parsed README.
     */
    public function getRepository(string $owner, string $repo): JsonResponse
    {
        try {
            $user = auth()->user();
            $token = $this->tokenRepository->getByUserId($user->id);

            if (!$token || !$token->isValid()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GitHub account not connected',
                ], 401);
            }

            // Check access
            if (!$this->apiService->checkRepositoryAccess($token, $owner, $repo)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Repository not found or access denied',
                ], 404);
            }

            // Fetch repository data
            $repoData = $this->apiService->getRepository($token, $owner, $repo);
            $readme = $this->apiService->getReadme($token, $owner, $repo, $repoData['default_branch'] ?? 'main');
            $languages = $this->apiService->getLanguages($token, $owner, $repo);
            $topics = $this->apiService->getTopics($token, $owner, $repo);

            // Parse README
            $parserService = app(\App\Services\GitHub\ReadmeParserService::class);
            $parsedData = $readme ? $parserService->parse($readme) : [];

            // Check if already imported
            $isImported = $this->importRepository->isImported($user->id, $owner, $repo);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'repository' => $repoData,
                    'readme' => $readme,
                    'parsed' => $parsedData,
                    'languages' => $languages,
                    'topics' => $topics,
                    'is_imported' => $isImported,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch GitHub repository', [
                'owner' => $owner,
                'repo' => $repo,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch repository: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import repository as project.
     */
    public function importRepository(ImportRepositoryRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $token = $this->tokenRepository->getByUserId($user->id);

            if (!$token || !$token->isValid()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GitHub account not connected',
                ], 401);
            }

            $owner = $request->input('owner');
            $repo = $request->input('repo');
            $userOverrides = $request->only([
                'title',
                'description',
                'short_description',
                'long_description',
                'demo_url',
                'project_type',
                'is_sellable',
                'features',
                'tags',
            ]);

            // Import repository
            $project = $this->importService->importRepository(
                $token,
                $owner,
                $repo,
                $userOverrides
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Repository imported successfully',
                'data' => new ProjectResource($project),
            ], 201);
        } catch (Exception $e) {
            Log::error('GitHub import failed', [
                'user_id' => auth()->id(),
                'owner' => $request->input('owner'),
                'repo' => $request->input('repo'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import repository: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check GitHub connection status.
     */
    public function status(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'connected' => false,
                    ],
                ]);
            }

            $token = $this->tokenRepository->getByUserId($user->id);

            if (!$token) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'connected' => false,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'connected' => true,
                    'valid' => $token->isValid(),
                    'github_username' => $token->github_username,
                    'github_avatar' => $token->github_avatar_url,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('GitHub status check failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check GitHub status',
            ], 500);
        }
    }

    /**
     * Disconnect GitHub account.
     */
    public function disconnect(): JsonResponse
    {
        try {
            $user = auth()->user();
            $token = $this->tokenRepository->getByUserId($user->id);

            if ($token) {
                // Revoke token on GitHub
                $this->oauthService->revokeToken($token->access_token);
                
                // Delete token
                $this->tokenRepository->deleteByUserId($user->id);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'GitHub account disconnected successfully',
            ]);
        } catch (Exception $e) {
            Log::error('GitHub disconnect failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to disconnect GitHub account',
            ], 500);
        }
    }
}
