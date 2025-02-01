<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\PostController;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\User\AnswerController;
use App\Http\Controllers\v1\Api\User\QuestionController;
use App\Http\Controllers\v1\Api\UserController;

Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/auth/otp', 'handleOtp');
    });
    // Authenticated Routes
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // User Routes
        Route::controller(UserController::class)->group(function () {
            Route::get('/user', 'user');
            Route::post('/user', 'update');
        });
        
        // Question Routes
        Route::apiResource('questions', QuestionController::class)->except(['create', 'edit']);
        Route::post('questions/{question}/upvote', [QuestionController::class, 'upvote'])->name('questions.upvote');
        
        // FEED Posts Routes
        // Route::get('posts/duplicate/{post_code}', [PostController::class, 'duplicate']);
        Route::post('/post/comment', [PostController::class, 'add_comment']);
        Route::delete('/post/comment/{id}', [PostController::class, 'delete_comment']);
        Route::get('comment/like-unlike/{id}', [PostController::class, 'like_unlike_comment']);
        
        Route::middleware(['throttle:30,1'])->get('posts/mention-suggestions', [PostController::class, 'mentionSuggestions']);
        Route::get('posts/like-unlike/{post_code}', [PostController::class, 'like_unlike']);
        Route::apiResource('posts', PostController::class)->except(['show','index']);

        // Answer Routes
        Route::prefix('questions/{question}')->group(function () {
            Route::apiResource('answers', AnswerController::class)
                ->only(['index', 'store'])
                ->shallow();
        });

        // Additional Answer Routes
        Route::prefix('answers/{answer}')->group(function () {
            Route::get('replies', [AnswerController::class, 'getReplies'])->name('answers.replies');
            Route::post('replies', [AnswerController::class, 'storeReply'])->name('answers.storeReply');
        });
    });
    
    Route::post('feed', [PostController::class, 'index']);
    Route::get('posts/{post_code}', [PostController::class, 'show']);
});
