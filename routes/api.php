<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\PostController;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\ProjectController;
use App\Http\Controllers\v1\Api\User\AnswerController;
use App\Http\Controllers\v1\Api\User\QuestionController;
use App\Http\Controllers\v1\Api\User\SocialLinkController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\v1\Api\UserController;
use App\Http\Controllers\v1\Api\SearchController;
use App\Http\Controllers\v1\Api\FollowController;
use App\Http\Controllers\v1\Api\ActivityController;
use App\Http\Controllers\v1\Api\NotificationController;
use App\Http\Controllers\v1\Api\SettingsController;
use App\Http\Controllers\v1\Api\PushSubscriptionController;
use App\Http\Controllers\v1\Api\BookmarkController;
use App\Http\Controllers\v1\Api\TaskController;

Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login')->name('login');
        Route::post('/auth/otp', 'handleOtp');
    });

    // Search Routes
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/tag-suggestions', [SearchController::class, 'tagSuggestions']);
    Route::get('/search/user-suggestions', [SearchController::class, 'userSuggestions']);

    // Feed Sidebar Routes (Authenticated)
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/feed-sidebar', [PostController::class, 'feedSidebar']);
    });

    // Activity Routes
    Route::get('/activities', [ActivityController::class, 'index']);

    // Public User Profile Route
    Route::get('/users/{username}', [UserController::class, 'show'])->name('users.show');

    // Notification Routes (Authenticated)
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::post('/notifications/bulk-delete', [NotificationController::class, 'bulkDelete']);
    });

    // Settings Routes (Authenticated)
    Route::middleware(['auth:api'])->group(function () {
        // Notification Settings
        Route::get('/settings/notifications', [SettingsController::class, 'getNotifications']);
        Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications']);
        
        // Push Subscription Routes
        Route::get('/push-subscriptions', [PushSubscriptionController::class, 'index']);
        Route::post('/push-subscriptions', [PushSubscriptionController::class, 'subscribe']);
        Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'unsubscribe']);
        
        // Add more settings endpoints here as needed
        // Route::get('/settings/privacy', [SettingsController::class, 'getPrivacy']);
        // Route::put('/settings/privacy', [SettingsController::class, 'updatePrivacy']);
    });
    
    // Public VAPID key route (needed for subscription)
    Route::get('/push-subscriptions/vapid-key', [PushSubscriptionController::class, 'getVapidKey']);

    // Authenticated Routes
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/global-data', [HomeController::class, 'globalData']);

        // User Routes
        Route::controller(UserController::class)->group(function () {
            Route::get('/user', 'user');
            Route::post('/user', 'update');
        });

        // Follow/Unfollow Routes
        Route::prefix('users/{username}')->group(function () {
            Route::post('/follow', [FollowController::class, 'toggle'])->name('users.follow');
            Route::get('/follow/check', [FollowController::class, 'check'])->name('users.follow.check');
            Route::get('/followers', [FollowController::class, 'followers'])->name('users.followers');
            Route::get('/following', [FollowController::class, 'following'])->name('users.following');
        });

        // Bookmark Routes
        Route::prefix('bookmarks')->group(function () {
            Route::post('/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
            Route::get('/check', [BookmarkController::class, 'check'])->name('bookmarks.check');
            Route::get('/count', [BookmarkController::class, 'count'])->name('bookmarks.count');
            Route::post('/batch-check', [BookmarkController::class, 'batchCheck'])->name('bookmarks.batch-check');
            Route::get('/', [BookmarkController::class, 'index'])->name('bookmarks.index');
            Route::delete('/{id}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
        });

        Route::get('/social-links/types', [SocialLinkController::class, 'types']);
        Route::apiResource('/social-links', SocialLinkController::class);

        // Task Routes
        Route::controller(TaskController::class)->prefix('tasks')->group(function () {
            Route::get('/daily/{user}', 'daily');
            Route::get('/weekly/{user}', 'weekly');
            Route::post('/complete', 'complete');
            Route::post('/assign', 'assign');
            Route::post('/auto-complete', 'autoComplete');
        });

        // Question Routes
        Route::post('questions/{id}/toggle-upvote', [QuestionController::class, 'toggleUpvote'])->name('questions.toggleUpvote');
        Route::get('questions/like-unlike/{slug}', [QuestionController::class, 'like_unlike']);
        Route::apiResource('questions', QuestionController::class)->except(['index', 'create', 'edit', 'show']);

        // FEED Posts Routes
        // Route::get('posts/duplicate/{post_code}', [PostController::class, 'duplicate']);
        Route::post('/post/comment', [PostController::class, 'add_comment']);
        Route::delete('/post/comment/{id}', [PostController::class, 'delete_comment']);
        Route::get('comment/like-unlike/{id}', [PostController::class, 'like_unlike_comment']);

        Route::middleware(['throttle:30,1'])->get('posts/mention-suggestions', [PostController::class, 'mentionSuggestions']);
        Route::get('posts/like-unlike/{post_code}', [PostController::class, 'like_unlike']);
        Route::apiResource('posts', PostController::class)->except(['show', 'create', 'edit', 'index']);

        // Answer Routes
        Route::prefix('questions/{question}')->group(function () {
            Route::apiResource('answers', AnswerController::class)
                ->only(['index', 'store'])
                ->shallow();
        });

        // Additional Answer Routes
        Route::prefix('answers/{answer}')->group(function () {
            Route::post('upvote', [AnswerController::class, 'upvote'])->name('answers.upvote');
            Route::get('replies', [AnswerController::class, 'getReplies'])->name('answers.replies');
            Route::post('replies', [AnswerController::class, 'storeReply'])->name('answers.storeReply');
        });
    });

    // Project Routes
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/featured', [ProjectController::class, 'featured']);
    Route::get('projects/trending', [ProjectController::class, 'trending']);
    Route::get('projects/technologies', [ProjectController::class, 'getTechnologies']);
    Route::get('projects/categories', [ProjectController::class, 'getCategories']);
    Route::get('projects/categories/{id}', [ProjectController::class, 'getCategory']);
    Route::get('projects/categories/{categoryId}/projects', [ProjectController::class, 'getProjectsByCategory']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::get('projects/{project}/reviews', [ProjectController::class, 'getReviews']);
    Route::get('projects/{project}/versions', [ProjectController::class, 'getVersions']);
    Route::post('projects/{project}/download', [ProjectController::class, 'download']);

    Route::middleware(['auth:api'])->group(function () {
        Route::apiResource('projects', ProjectController::class)->except(['index', 'show']);
        Route::post('projects/{project}/publish', [ProjectController::class, 'publish']);
        Route::post('projects/{project}/submit-review', [ProjectController::class, 'submitForReview']);
        Route::post('projects/{project}/upvote', [ProjectController::class, 'upvote']);
        Route::post('projects/{project}/fund', [ProjectController::class, 'fund']);
        Route::get('projects/drafts', [ProjectController::class, 'drafts']);
        
        // Reviews
        Route::post('projects/{project}/reviews', [ProjectController::class, 'createReview']);
        Route::put('reviews/{reviewId}', [ProjectController::class, 'updateReview']);
        Route::delete('reviews/{reviewId}', [ProjectController::class, 'deleteReview']);
        
        // Versions
        Route::post('projects/{project}/versions', [ProjectController::class, 'createVersion']);
        
        Route::post('technologies', [ProjectController::class, 'createTechnology']);
        
        // GitHub Integration Routes
        Route::prefix('github')->group(function () {
            Route::get('authorize', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'authorize']);
            Route::get('status', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'status']);
            Route::get('repositories', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'getRepositories']);
            Route::get('repositories/{owner}/{repo}', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'getRepository']);
            Route::post('import', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'importRepository']);
            Route::delete('disconnect', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'disconnect']);
        });
    });
    
    // GitHub OAuth Callback (public route)
    Route::get('github/callback', [\App\Http\Controllers\v1\Api\GitHub\GitHubController::class, 'callback']);

    // Question Public Routes
    Route::controller(QuestionController::class)->group(function () {
        Route::post('/questions-feed', 'index');
        Route::get('/questions/{id}', 'show');
    });

    // Feed Public Routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::controller(PostController::class)->group(function () {
        Route::post('feed', 'index');
        Route::get('posts/{post_code}', 'show');
    });
});
