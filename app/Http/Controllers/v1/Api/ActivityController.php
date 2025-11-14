<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Get user activities with pagination
     */
    public function index(Request $request)
    {
        $perPage = min((int)$request->input('per_page', 15), 50);
        $username = $request->input('username');

        $query = Activity::with(['user:id,name,username'])
            ->with(['subject' => function ($morphTo) {
                // Eager load relationships based on subject type
                $morphTo->morphWith([
                    \App\Models\Post::class => ['user:id,name,username'],
                    \App\Models\Question::class => ['user:id,name,username'],
                    \App\Models\Answer::class => ['user:id,name,username', 'question:id,title,slug'],
                    \App\Models\Comment::class => ['user:id,name,username'],
                    \App\Models\Like::class => ['user:id,name,username'],
                    \App\Models\Follower::class => ['follower:id,name,username', 'following:id,name,username'],
                ]);
            }])
            ->orderBy('created_at', 'desc');

        // Filter by username if provided
        if ($username) {
            $query->whereHas('user', function ($q) use ($username) {
                $q->where('username', $username);
            });
        } else {
            // If no username, show activities from users the authenticated user follows
            // For now, show all activities (can be filtered later)
        }

        $activities = $query->paginate($perPage);

        // Transform activities for frontend
        $transformedActivities = $activities->getCollection()->map(function ($activity) {
            return $this->transformActivity($activity);
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'activities' => $transformedActivities,
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                    'last_page' => $activities->lastPage(),
                    'has_more' => $activities->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Transform activity for frontend
     */
    private function transformActivity(Activity $activity): array
    {
        $baseData = [
            'id' => $activity->id,
            'type' => $activity->activity_type,
            'description' => $activity->description,
            'metadata' => $activity->metadata,
            'created_at' => $activity->created_at->toISOString(),
            'created_at_human' => $activity->created_at->diffForHumans(),
            'user' => [
                'id' => $activity->user->id,
                'name' => $activity->user->name,
                'username' => $activity->user->username,
                'profile_photo' => $activity->user->profile_photo ?? '', // Accessor will be available
            ],
        ];

        // Add subject data based on type
        if ($activity->subject) {
            switch ($activity->activity_type) {
                case Activity::TYPE_POST_CREATED:
                    $baseData['subject'] = [
                        'id' => $activity->subject->id,
                        'post_code' => $activity->subject->post_code,
                        'title' => $activity->subject->title,
                        'content' => $activity->subject->content,
                        'user' => [
                            'id' => $activity->subject->user->id,
                            'name' => $activity->subject->user->name,
                            'username' => $activity->subject->user->username,
                        ],
                    ];
                    break;

                case Activity::TYPE_QUESTION_CREATED:
                    $baseData['subject'] = [
                        'id' => $activity->subject->id,
                        'slug' => $activity->subject->slug,
                        'title' => $activity->subject->title,
                        'content' => $activity->subject->content,
                        'user' => [
                            'id' => $activity->subject->user->id,
                            'name' => $activity->subject->user->name,
                            'username' => $activity->subject->user->username,
                        ],
                    ];
                    break;

                case Activity::TYPE_ANSWER_CREATED:
                    $baseData['subject'] = [
                        'id' => $activity->subject->id,
                        'content' => $activity->subject->content,
                        'question' => $activity->subject->question ? [
                            'id' => $activity->subject->question->id,
                            'title' => $activity->subject->question->title,
                            'slug' => $activity->subject->question->slug,
                        ] : null,
                        'user' => [
                            'id' => $activity->subject->user->id,
                            'name' => $activity->subject->user->name,
                            'username' => $activity->subject->user->username,
                        ],
                    ];
                    break;

                case Activity::TYPE_COMMENT_CREATED:
                    $baseData['subject'] = [
                        'id' => $activity->subject->id,
                        'content' => $activity->subject->content,
                        'user' => [
                            'id' => $activity->subject->user->id,
                            'name' => $activity->subject->user->name,
                            'username' => $activity->subject->user->username,
                        ],
                    ];
                    break;

                case Activity::TYPE_FOLLOW_CREATED:
                    $baseData['subject'] = [
                        'id' => $activity->subject->id,
                        'follower' => $activity->subject->follower ? [
                            'id' => $activity->subject->follower->id,
                            'name' => $activity->subject->follower->name,
                            'username' => $activity->subject->follower->username,
                        ] : null,
                        'following' => $activity->subject->following ? [
                            'id' => $activity->subject->following->id,
                            'name' => $activity->subject->following->name,
                            'username' => $activity->subject->following->username,
                        ] : null,
                    ];
                    break;
            }
        }

        return $baseData;
    }
}
