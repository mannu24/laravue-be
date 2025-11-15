<?php

namespace App\Services\GitHub;

use App\Models\Project;
use App\Models\GitHubImport;
use App\Models\UserGitHubToken;
use App\Repositories\ProjectRepository;
use App\Repositories\TechnologyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GitHubImportService
{
    public function __construct(
        private GitHubApiService $apiService,
        private ReadmeParserService $parserService,
        private ProjectRepository $projectRepository,
        private TechnologyRepository $technologyRepository
    ) {}

    /**
     * Import repository as project.
     */
    public function importRepository(
        UserGitHubToken $token,
        string $owner,
        string $repo,
        array $userOverrides = []
    ): Project {
        try {
            DB::beginTransaction();

            // Check if already imported
            $existingImport = GitHubImport::where('user_id', $token->user_id)
                ->where('github_full_name', "{$owner}/{$repo}")
                ->first();

            if ($existingImport) {
                throw new Exception("Repository {$owner}/{$repo} has already been imported.");
            }

            // Fetch repository data
            $repoData = $this->apiService->getRepository($token, $owner, $repo);
            $readme = $this->apiService->getReadme($token, $owner, $repo, $repoData['default_branch'] ?? 'main');
            $languages = $this->apiService->getLanguages($token, $owner, $repo);
            $topics = $this->apiService->getTopics($token, $owner, $repo);

            // Parse README
            $parsedData = $readme ? $this->parserService->parse($readme) : [];

            // Map GitHub data to Project model
            $projectData = $this->mapRepositoryToProject(
                $repoData,
                $parsedData,
                $languages,
                $topics,
                $userOverrides
            );

            // Create project
            $project = $this->projectRepository->create($projectData);

            // Attach technologies
            if (!empty($languages)) {
                $this->attachTechnologies($project, $languages);
            }

            // Create import record
            GitHubImport::create([
                'user_id' => $token->user_id,
                'project_id' => $project->id,
                'github_owner' => $owner,
                'github_repo' => $repo,
                'github_repo_id' => (string) $repoData['id'],
                'github_full_name' => "{$owner}/{$repo}",
                'imported_data' => [
                    'repository' => $repoData,
                    'readme' => $readme,
                    'parsed' => $parsedData,
                ],
                'imported_at' => now(),
            ]);

            DB::commit();

            return $project->fresh(['technologies', 'user']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('GitHub import failed', [
                'user_id' => $token->user_id,
                'repo' => "{$owner}/{$repo}",
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Map GitHub repository data to Project model.
     */
    private function mapRepositoryToProject(
        array $repoData,
        array $parsedData,
        array $languages,
        array $topics,
        array $userOverrides
    ): array {
        // Extract links
        $demoUrl = $repoData['homepage'] ?? null;
        $links = $parsedData['links'] ?? [];
        foreach ($links as $link) {
            $text = strtolower($link['text'] ?? '');
            $url = $link['url'] ?? '';
            
            if (stripos($text, 'demo') !== false || stripos($text, 'live') !== false) {
                $demoUrl = $url;
                break;
            }
        }

        // Extract license
        $license = $repoData['license']['spdx_id'] ?? $parsedData['license'] ?? null;
        $licenseUrl = $repoData['license']['url'] ?? null;

        // Build features array
        $features = $parsedData['features'] ?? [];
        if (empty($features) && !empty($repoData['description'])) {
            $features = [$repoData['description']];
        }

        // Combine description
        $description = $parsedData['description'] ?? $repoData['description'] ?? '';
        $longDescription = $parsedData['description'] ?? '';

        // Map data
        $data = [
            'user_id' => $userOverrides['user_id'] ?? auth()->id(),
            'title' => $userOverrides['title'] ?? $parsedData['title'] ?? $repoData['name'],
            'description' => $userOverrides['description'] ?? $description,
            'short_description' => $userOverrides['short_description'] ?? $parsedData['short_description'] ?? mb_substr($description, 0, 500),
            'long_description' => $userOverrides['long_description'] ?? $longDescription,
            'github_url' => $repoData['html_url'],
            'demo_url' => $userOverrides['demo_url'] ?? $demoUrl,
            'project_type' => $userOverrides['project_type'] ?? 'open',
            'is_sellable' => $userOverrides['is_sellable'] ?? false,
            'status' => 'draft', // Always start as draft for review
            'features' => $userOverrides['features'] ?? $features,
            'installation_guide' => $userOverrides['installation_guide'] ?? $parsedData['installation'],
            'requirements' => $userOverrides['requirements'] ?? $parsedData['requirements'],
            'license_type' => $userOverrides['license_type'] ?? $license,
            'license_url' => $userOverrides['license_url'] ?? $licenseUrl,
            'documentation_url' => $userOverrides['documentation_url'] ?? $repoData['html_url'] . '/blob/main/README.md',
            'tags' => $userOverrides['tags'] ?? $topics,
            'stars_count' => $repoData['stargazers_count'] ?? 0,
            'forks_count' => $repoData['forks_count'] ?? 0,
            'is_active' => true,
            'is_maintained' => !($repoData['archived'] ?? false),
            'last_updated_at' => $repoData['updated_at'] ? date('Y-m-d H:i:s', strtotime($repoData['updated_at'])) : null,
        ];

        // Merge user overrides
        return array_merge($data, array_filter($userOverrides, fn($key) => !in_array($key, ['user_id']), ARRAY_FILTER_USE_KEY));
    }

    /**
     * Attach technologies to project.
     */
    private function attachTechnologies(Project $project, array $languages): void
    {
        if (empty($languages)) {
            return;
        }

        // Process technologies and get IDs
        $technologyIds = $this->technologyRepository->processTechnologyNames(
            $languages,
            $project->user_id
        );

        if (!empty($technologyIds)) {
            $project->technologies()->sync($technologyIds);
        }
    }

    /**
     * Check if repository is already imported.
     */
    public function isImported(int $userId, string $owner, string $repo): bool
    {
        return GitHubImport::where('user_id', $userId)
            ->where('github_full_name', "{$owner}/{$repo}")
            ->exists();
    }

    /**
     * Get import history for user.
     */
    public function getImportHistory(int $userId, int $limit = 10): array
    {
        return GitHubImport::where('user_id', $userId)
            ->with('project')
            ->orderBy('imported_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}

