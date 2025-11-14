<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SearchController extends Controller
{
    use HttpResponse;

    /**
     * Global search across posts, questions, users, and projects
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));
        $type = $request->input('type', 'all'); // all, post, question, user, project
        $author = $request->input('author'); // username or user_id
        $tags = $request->input('tags'); // comma-separated tag names
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sort = $request->input('sort', 'relevance'); // relevance, date, trending, popular
        $limit = min((int)$request->input('limit', 10), 50); // Max 50 results per type
        $page = max((int)$request->input('page', 1), 1); // Current page
        $perPage = (int)$request->input('per_page', 25); // Results per page (default 25 for full page, 10 for dropdown)
        $includeTrending = $request->boolean('include_trending', false);
        $includeRecommended = $request->boolean('include_recommended', false);

        // Validate query
        if (empty($query) && $type === 'all' && !$includeTrending && !$includeRecommended) {
            return $this->error('Search query is required', 400);
        }

        $results = [
            'posts' => [],
            'questions' => [],
            'users' => [],
            'projects' => [],
            'trending' => [],
            'recommended' => [],
            'meta' => [
                'query' => $query,
                'total_results' => 0,
                'search_time' => 0,
            ]
        ];

        $startTime = microtime(true);

        // Determine if we're using pagination (per_page provided) or simple limit (for dropdown)
        $usePagination = $perPage > 0 && $request->has('per_page');
        
        if ($usePagination) {
            // For pagination: get results per type, then combine and paginate
            // When type=all, we need to get enough from each type to fill the page
            $resultsPerType = $type === 'all' ? ceil($perPage / 4) : $perPage; // Distribute across 4 types
            $offset = ($page - 1) * $perPage;
            
            // Search Posts
            if ($type === 'all' || $type === 'post') {
                $results['posts'] = $this->searchPosts($query, $author, $tags, $dateFrom, $dateTo, $sort, $resultsPerType * 2, 0);
            }

            // Search Questions
            if ($type === 'all' || $type === 'question') {
                $results['questions'] = $this->searchQuestions($query, $author, $tags, $dateFrom, $dateTo, $sort, $resultsPerType * 2, 0);
            }

            // Search Users
            if ($type === 'all' || $type === 'user') {
                $results['users'] = $this->searchUsers($query, $sort, $resultsPerType * 2, 0);
            }

            // Search Projects
            if ($type === 'all' || $type === 'project') {
                $results['projects'] = $this->searchProjects($query, $author, $tags, $dateFrom, $dateTo, $sort, $resultsPerType * 2, 0);
            }
        } else {
            // For simple limit (dropdown): get limit per type, frontend will combine and slice
            // Search Posts
            if ($type === 'all' || $type === 'post') {
                $results['posts'] = $this->searchPosts($query, $author, $tags, $dateFrom, $dateTo, $sort, $limit, 0);
            }

            // Search Questions
            if ($type === 'all' || $type === 'question') {
                $results['questions'] = $this->searchQuestions($query, $author, $tags, $dateFrom, $dateTo, $sort, $limit, 0);
            }

            // Search Users
            if ($type === 'all' || $type === 'user') {
                $results['users'] = $this->searchUsers($query, $sort, $limit, 0);
            }

            // Search Projects
            if ($type === 'all' || $type === 'project') {
                $results['projects'] = $this->searchProjects($query, $author, $tags, $dateFrom, $dateTo, $sort, $limit, 0);
            }
        }

        // Get Trending Content
        if ($includeTrending || empty($query)) {
            $results['trending'] = $this->getTrendingContent($limit);
        }

        // Get Recommended Content
        if ($includeRecommended && auth()->guard('api')->check()) {
            $results['recommended'] = $this->getRecommendedContent(auth()->guard('api')->id(), $limit);
        }

        // Get total counts for pagination (only if using pagination)
        if ($usePagination) {
            $totalPosts = $this->getPostsCount($query, $author, $tags, $dateFrom, $dateTo);
            $totalQuestions = $this->getQuestionsCount($query, $author, $tags, $dateFrom, $dateTo);
            $totalUsers = $this->getUsersCount($query);
            $totalProjects = $this->getProjectsCount($query, $author, $tags, $dateFrom, $dateTo);
            
            // Calculate totals based on type filter
            if ($type === 'all') {
                $totalResults = $totalPosts + $totalQuestions + $totalUsers + $totalProjects;
            } else {
                $totalResults = match($type) {
                    'post' => $totalPosts,
                    'question' => $totalQuestions,
                    'user' => $totalUsers,
                    'project' => $totalProjects,
                    default => 0
                };
            }
        } else {
            // For simple limit (dropdown), just count returned results
            $totalResults = count($results['posts']) + count($results['questions']) + count($results['users']) + count($results['projects']);
        }

        $results['meta']['total_results'] = $totalResults;
        $results['meta']['search_time'] = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds
        
        if ($usePagination) {
            $offset = ($page - 1) * $perPage;
            $results['meta']['has_more'] = $totalResults > ($offset + $perPage);
            $results['meta']['current_page'] = $page;
            $results['meta']['per_page'] = $perPage;
            $results['meta']['total_pages'] = max(1, ceil($totalResults / $perPage));
        } else {
            // For dropdown: check if we have more results than the limit
            $currentCount = count($results['posts']) + count($results['questions']) + count($results['users']) + count($results['projects']);
            $results['meta']['has_more'] = $currentCount >= $limit;
            $results['meta']['current_page'] = 1;
            $results['meta']['per_page'] = null;
            $results['meta']['total_pages'] = 1;
        }

        return $this->success(
            data: $results,
            message: 'Search completed successfully'
        );
    }

    /**
     * Get posts count for pagination
     */
    private function getPostsCount($query, $author, $tags, $dateFrom, $dateTo)
    {
        $postsQuery = Post::query()->where('is_blocked', false);

        if (!empty($query)) {
            $postsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");
            });
        }

        if ($author) {
            $postsQuery->whereHas('user', function ($q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('id', $author);
                } else {
                    $q->where('username', 'LIKE', "%{$author}%")
                      ->orWhere('name', 'LIKE', "%{$author}%");
                }
            });
        }

        if ($tags) {
            $tagNames = is_array($tags) ? $tags : explode(',', $tags);
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
            if ($tagIds->isNotEmpty()) {
                $postsQuery->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
            }
        }

        if ($dateFrom) {
            $postsQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $postsQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        return $postsQuery->count();
    }

    /**
     * Search posts with filters
     */
    private function searchPosts($query, $author, $tags, $dateFrom, $dateTo, $sort, $limit, $offset = 0)
    {
        $postsQuery = Post::query()
            ->with(['user:id,name,username'])
            ->withCount(['likes', 'comments'])
            ->where('is_blocked', false);

        // Text search
        if (!empty($query)) {
            $postsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");
            });
        }

        // Author filter
        if ($author) {
            $postsQuery->whereHas('user', function ($q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('id', $author);
                } else {
                    $q->where('username', 'LIKE', "%{$author}%")
                      ->orWhere('name', 'LIKE', "%{$author}%");
                }
            });
        }

        // Tags filter (if posts have tags)
        if ($tags) {
            $tagNames = is_array($tags) ? $tags : explode(',', $tags);
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
            if ($tagIds->isNotEmpty()) {
                $postsQuery->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
            }
        }

        // Date filters
        if ($dateFrom) {
            $postsQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $postsQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        // Sorting
        switch ($sort) {
            case 'trending':
                $postsQuery->orderByRaw('(likes_count * 2 + comments_count + views) DESC')
                          ->orderBy('created_at', 'DESC');
                break;
            case 'popular':
                $postsQuery->orderByRaw('(likes_count + comments_count) DESC')
                          ->orderBy('created_at', 'DESC');
                break;
            case 'date':
                $postsQuery->orderBy('created_at', 'DESC');
                break;
            default: // relevance
                if (!empty($query)) {
                    $postsQuery->orderByRaw("
                        CASE 
                            WHEN title LIKE ? THEN 1
                            WHEN title LIKE ? THEN 2
                            WHEN content LIKE ? THEN 3
                            ELSE 4
                        END
                    ", ["{$query}", "%{$query}%", "%{$query}%"])
                    ->orderBy('created_at', 'DESC');
                } else {
                    $postsQuery->orderBy('created_at', 'DESC');
                }
        }

        $posts = $postsQuery->offset($offset)->limit($limit)->get();

        return $posts->map(function ($post) {
            return [
                'type' => 'post',
                'id' => $post->id,
                'post_code' => $post->post_code,
                'title' => $post->title,
                'content' => mb_substr(strip_tags($post->content), 0, 150) . '...',
                'url' => "/@{$post->user->username}/{$post->post_code}",
                'author' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'username' => $post->user->username,
                    'avatar' => $post->user->profile_photo,
                ],
                'stats' => [
                    'likes' => $post->likes_count ?? 0,
                    'comments' => $post->comments_count ?? 0,
                    'views' => $post->views ?? 0,
                ],
                'posted_at' => $post->posted_at,
                'icon' => 'FileText',
            ];
        })->toArray();
    }

    /**
     * Get questions count for pagination
     */
    private function getQuestionsCount($query, $author, $tags, $dateFrom, $dateTo)
    {
        $questionsQuery = Question::query()->where('is_closed', false);

        if (!empty($query)) {
            $questionsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");
            });
        }

        if ($author) {
            $questionsQuery->whereHas('user', function ($q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('id', $author);
                } else {
                    $q->where('username', 'LIKE', "%{$author}%")
                      ->orWhere('name', 'LIKE', "%{$author}%");
                }
            });
        }

        if ($tags) {
            $tagNames = is_array($tags) ? $tags : explode(',', $tags);
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
            if ($tagIds->isNotEmpty()) {
                $questionsQuery->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
            }
        }

        if ($dateFrom) {
            $questionsQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $questionsQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        return $questionsQuery->count();
    }

    /**
     * Search questions with filters
     */
    private function searchQuestions($query, $author, $tags, $dateFrom, $dateTo, $sort, $limit, $offset = 0)
    {
        $questionsQuery = Question::query()
            ->with(['user:id,name,username'])
            ->withCount(['likes', 'answers', 'upvotes'])
            ->where('is_closed', false);

        // Text search
        if (!empty($query)) {
            $questionsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");
            });
        }

        // Author filter
        if ($author) {
            $questionsQuery->whereHas('user', function ($q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('id', $author);
                } else {
                    $q->where('username', 'LIKE', "%{$author}%")
                      ->orWhere('name', 'LIKE', "%{$author}%");
                }
            });
        }

        // Tags filter
        if ($tags) {
            $tagNames = is_array($tags) ? $tags : explode(',', $tags);
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
            if ($tagIds->isNotEmpty()) {
                $questionsQuery->whereHas('tags', function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                });
            }
        }

        // Date filters
        if ($dateFrom) {
            $questionsQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $questionsQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        // Sorting
        switch ($sort) {
            case 'trending':
                $questionsQuery->orderByRaw('(upvotes_count * 2 + answers_count + view_count) DESC')
                              ->orderBy('last_activity_date', 'DESC');
                break;
            case 'popular':
                $questionsQuery->orderByRaw('(upvotes_count + answers_count) DESC')
                              ->orderBy('view_count', 'DESC');
                break;
            case 'date':
                $questionsQuery->orderBy('created_at', 'DESC');
                break;
            default: // relevance
                if (!empty($query)) {
                    $questionsQuery->orderByRaw("
                        CASE 
                            WHEN title LIKE ? THEN 1
                            WHEN title LIKE ? THEN 2
                            WHEN content LIKE ? THEN 3
                            ELSE 4
                        END
                    ", ["{$query}", "%{$query}%", "%{$query}%"])
                    ->orderBy('created_at', 'DESC');
                } else {
                    $questionsQuery->orderBy('created_at', 'DESC');
                }
        }

        $questions = $questionsQuery->offset($offset)->limit($limit)->get();

        return $questions->map(function ($question) {
            return [
                'type' => 'question',
                'id' => $question->id,
                'slug' => $question->slug,
                'title' => $question->title,
                'content' => mb_substr(strip_tags($question->content), 0, 150) . '...',
                'url' => "/qna/{$question->slug}",
                'author' => [
                    'id' => $question->user->id ?? null,
                    'name' => $question->user->name ?? 'Anonymous',
                    'username' => $question->user->username ?? null,
                    'avatar' => $question->user->profile_photo ?? null,
                ],
                'stats' => [
                    'likes' => $question->likes_count ?? 0,
                    'answers' => $question->answers_count ?? 0,
                    'upvotes' => $question->upvotes_count ?? 0,
                    'views' => $question->view_count ?? 0,
                ],
                'is_solved' => $question->is_solved,
                'tags' => $question->tags->pluck('name')->toArray(),
                'posted_at' => $question->posted_at,
                'icon' => 'MessageSquare',
            ];
        })->toArray();
    }

    /**
     * Get users count for pagination
     */
    private function getUsersCount($query)
    {
        if (empty($query)) {
            return 0;
        }

        return User::query()
            ->where(function ($q) use ($query) {
                $q->where('username', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->count();
    }

    /**
     * Search users
     */
    private function searchUsers($query, $sort = 'relevance', $limit, $offset = 0)
    {
        if (empty($query)) {
            return [];
        }

        $usersQuery = User::query()
            ->where(function ($q) use ($query) {
                $q->where('username', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            });

        // Sorting
        switch ($sort) {
            case 'date':
                $usersQuery->orderBy('created_at', 'DESC');
                break;
            case 'popular':
                // Order by followers count (most popular users first)
                $usersQuery->withCount('followers')
                          ->orderBy('followers_count', 'DESC')
                          ->orderBy('created_at', 'DESC');
                break;
            case 'trending':
                // Order by recent activity (users with recent posts/activity)
                // For now, use followers count as a proxy for trending
                $usersQuery->withCount('followers')
                          ->orderBy('followers_count', 'DESC')
                          ->orderBy('created_at', 'DESC');
                break;
            default: // relevance
                $usersQuery->orderByRaw("
                    CASE 
                        WHEN username LIKE ? THEN 1
                        WHEN name LIKE ? THEN 2
                        ELSE 3
                    END
                ", ["{$query}", "%{$query}%"])
                ->orderBy('created_at', 'DESC');
        }

        $users = $usersQuery->offset($offset)
            ->limit($limit)
            ->get();

        return $users->map(function ($user) {
            return [
                'type' => 'user',
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'url' => "/@{$user->username}",
                'avatar' => $user->profile_photo,
                'icon' => 'Users',
            ];
        })->toArray();
    }

    /**
     * Get projects count for pagination
     */
    private function getProjectsCount($query, $author, $tags, $dateFrom, $dateTo)
    {
        $projectsQuery = Project::query()->where('is_active', true);

        if (!empty($query)) {
            $projectsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        if ($author) {
            $projectsQuery->whereHas('user', function ($q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('id', $author);
                } else {
                    $q->where('username', 'LIKE', "%{$author}%")
                      ->orWhere('name', 'LIKE', "%{$author}%");
                }
            });
        }

        if ($tags) {
            $tagNames = is_array($tags) ? $tags : explode(',', $tags);
            $techIds = Technology::whereIn('name', $tagNames)->pluck('id');
            if ($techIds->isNotEmpty()) {
                $projectsQuery->whereHas('technologies', function ($q) use ($techIds) {
                    $q->whereIn('technologies.id', $techIds);
                });
            }
        }

        if ($dateFrom) {
            $projectsQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $projectsQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        return $projectsQuery->count();
    }

    /**
     * Search projects with filters
     */
    private function searchProjects($query, $author, $tags, $dateFrom, $dateTo, $sort, $limit, $offset = 0)
    {
        $projectsQuery = Project::query()
            ->with(['user:id,name,username'])
            ->where('is_active', true);

        // Text search
        if (!empty($query)) {
            $projectsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        // Author filter
        if ($author) {
            $projectsQuery->whereHas('user', function ($q) use ($author) {
                if (is_numeric($author)) {
                    $q->where('id', $author);
                } else {
                    $q->where('username', 'LIKE', "%{$author}%")
                      ->orWhere('name', 'LIKE', "%{$author}%");
                }
            });
        }

        // Tags/Technologies filter
        if ($tags) {
            $tagNames = is_array($tags) ? $tags : explode(',', $tags);
            $techIds = Technology::whereIn('name', $tagNames)->pluck('id');
            if ($techIds->isNotEmpty()) {
                $projectsQuery->whereHas('technologies', function ($q) use ($techIds) {
                    $q->whereIn('technologies.id', $techIds);
                });
            }
        }

        // Date filters
        if ($dateFrom) {
            $projectsQuery->whereDate('created_at', '>=', Carbon::parse($dateFrom));
        }
        if ($dateTo) {
            $projectsQuery->whereDate('created_at', '<=', Carbon::parse($dateTo));
        }

        // Sorting
        switch ($sort) {
            case 'trending':
            case 'popular':
                $projectsQuery->orderBy('views', 'DESC');
                break;
            case 'date':
                $projectsQuery->orderBy('created_at', 'DESC');
                break;
            default: // relevance
                if (!empty($query)) {
                    $projectsQuery->orderByRaw("
                        CASE 
                            WHEN title LIKE ? THEN 1
                            WHEN title LIKE ? THEN 2
                            WHEN description LIKE ? THEN 3
                            ELSE 4
                        END
                    ", ["{$query}", "%{$query}%", "%{$query}%"])
                    ->orderBy('created_at', 'DESC');
                } else {
                    $projectsQuery->orderBy('created_at', 'DESC');
                }
        }

        $projects = $projectsQuery->offset($offset)->limit($limit)->get();

        return $projects->map(function ($project) {
            return [
                'type' => 'project',
                'id' => $project->id,
                'slug' => $project->slug,
                'title' => $project->title,
                'description' => mb_substr(strip_tags($project->description), 0, 150) . '...',
                'url' => "/projects/{$project->id}",
                'author' => [
                    'id' => $project->user->id ?? null,
                    'name' => $project->user->name ?? 'Unknown',
                    'username' => $project->user->username ?? null,
                    'avatar' => $project->user->profile_photo ?? null,
                ],
                'project_type' => $project->project_type,
                'stats' => [
                    'views' => $project->views ?? 0,
                ],
                'icon' => 'FolderOpen',
            ];
        })->toArray();
    }

    /**
     * Get trending content (last 7 days)
     */
    private function getTrendingContent($limit = 10)
    {
        $cacheKey = 'trending_content_' . $limit;
        
        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            $sevenDaysAgo = Carbon::now()->subDays(7);
            
            // Trending Posts
            $trendingPosts = Post::query()
                ->with(['user:id,name,username'])
                ->withCount(['likes', 'comments'])
                ->where('is_blocked', false)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->orderByRaw('(likes_count * 2 + comments_count + views) DESC')
                ->limit($limit)
                ->get()
                ->map(function ($post) {
                    return [
                        'type' => 'post',
                        'id' => $post->id,
                        'post_code' => $post->post_code,
                        'title' => $post->title,
                        'url' => "/@{$post->user->username}/post_{$post->post_code}",
                        'author' => $post->user->username,
                        'trending_score' => ($post->likes_count * 2) + $post->comments_count + $post->views,
                        'icon' => 'FileText',
                    ];
                });

            // Trending Questions
            $trendingQuestions = Question::query()
                ->with(['user:id,name,username'])
                ->withCount(['upvotes', 'answers'])
                ->where('is_closed', false)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->orderByRaw('(upvotes_count * 2 + answers_count + view_count) DESC')
                ->limit($limit)
                ->get()
                ->map(function ($question) {
                    return [
                        'type' => 'question',
                        'id' => $question->id,
                        'slug' => $question->slug,
                        'title' => $question->title,
                        'url' => "/qna/{$question->slug}",
                        'author' => $question->user->username ?? 'Anonymous',
                        'trending_score' => ($question->upvotes_count * 2) + $question->answers_count + $question->view_count,
                        'icon' => 'MessageSquare',
                    ];
                });

            return [
                'posts' => $trendingPosts->toArray(),
                'questions' => $trendingQuestions->toArray(),
            ];
        });
    }

    /**
     * Get recommended content based on user activity
     */
    private function getRecommendedContent($userId, $limit = 10)
    {
        $cacheKey = "recommended_content_{$userId}_{$limit}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $limit) {
            // Get user's liked tags
            $userLikedTags = DB::table('likes')
                ->join('tag_associates', function ($join) {
                    $join->on('likes.record_id', '=', 'tag_associates.record_id')
                         ->where('likes.record_type', '=', 'question');
                })
                ->join('tags', 'tag_associates.tag_id', '=', 'tags.id')
                ->where('likes.user_id', $userId)
                ->select('tags.id', 'tags.name')
                ->distinct()
                ->pluck('tags.id')
                ->toArray();

            $recommendations = [];

            // Recommend questions with similar tags
            if (!empty($userLikedTags)) {
                $recommendedQuestions = Question::query()
                    ->with(['user:id,name,username'])
                    ->withCount(['upvotes', 'answers'])
                    ->whereHas('tags', function ($q) use ($userLikedTags) {
                        $q->whereIn('tags.id', $userLikedTags);
                    })
                    ->where('is_closed', false)
                    ->where('user_id', '!=', $userId) // Don't recommend own content
                    ->orderBy('score', 'DESC')
                    ->limit($limit)
                    ->get()
                    ->map(function ($question) {
                        return [
                            'type' => 'question',
                            'id' => $question->id,
                            'slug' => $question->slug,
                            'title' => $question->title,
                            'url' => "/qna/{$question->slug}",
                            'author' => $question->user->username ?? 'Anonymous',
                            'icon' => 'MessageSquare',
                        ];
                    });

                $recommendations['questions'] = $recommendedQuestions->toArray();
            }

            // Recommend posts from users you follow (if follow system exists)
            // For now, recommend popular posts
            $recommendedPosts = Post::query()
                ->with(['user:id,name,username'])
                ->withCount(['likes', 'comments'])
                ->where('is_blocked', false)
                ->where('user_id', '!=', $userId)
                ->orderByRaw('(likes_count + comments_count) DESC')
                ->limit($limit)
                ->get()
                ->map(function ($post) {
                    return [
                        'type' => 'post',
                        'id' => $post->id,
                        'post_code' => $post->post_code,
                        'title' => $post->title,
                        'url' => "/@{$post->user->username}/post_{$post->post_code}",
                        'author' => $post->user->username,
                        'icon' => 'FileText',
                    ];
                });

            $recommendations['posts'] = $recommendedPosts->toArray();

            return $recommendations;
        });
    }

    /**
     * Get tag suggestions for autocomplete
     */
    public function tagSuggestions(Request $request)
    {
        $query = trim($request->input('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return $this->success(
                data: ['tags' => []],
                message: 'Tag suggestions retrieved successfully'
            );
        }

        $tags = Tag::where('name', 'LIKE', "%{$query}%")
            ->orderBy('name', 'ASC')
            ->limit(20)
            ->pluck('name')
            ->toArray();

        return $this->success(
            data: ['tags' => $tags],
            message: 'Tag suggestions retrieved successfully'
        );
    }

    /**
     * Get user suggestions for autocomplete (for author filter)
     */
    public function userSuggestions(Request $request)
    {
        $query = trim($request->input('q', ''));
        
        if (empty($query) || strlen($query) < 2) {
            return $this->success(
                data: ['users' => []],
                message: 'User suggestions retrieved successfully'
            );
        }

        $users = User::where('username', 'LIKE', "%{$query}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->select('id', 'username', 'name', 'profile_photo')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'avatar' => $user->profile_photo,
                ];
            })
            ->toArray();

        return $this->success(
            data: ['users' => $users],
            message: 'User suggestions retrieved successfully'
        );
    }
}
