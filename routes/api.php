<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\Api\AuthController;
use App\Http\Controllers\v1\Api\User\AnswerController;
use App\Http\Controllers\v1\Api\User\QuestionController;
use App\Http\Controllers\v1\Api\UserController;

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/auth/otp', 'handleOtp');
    });

    Route::middleware(['auth:api'])->group(function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::controller(UserController::class)->group(function () {
            Route::get('user', 'user');
            Route::post('user', 'update');
        });

        // User Question Routes
        Route::prefix('questions')->group(function () {
            Route::controller(QuestionController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/latest', 'latest');
                Route::get('/{id}', 'show');
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
                Route::post('/{id}/upvote', 'upvote');
            });
        });

        // User Question's answer Routes
        Route::controller(AnswerController::class)->group(function () {
            Route::prefix('questions/{question}')->group(function () {
                Route::get('answers', 'index')->name('answers.index');
                Route::post('answers', 'store')->name('answers.store');
            });
            Route::prefix('answers/{answer}')->group(function () {
                Route::get('/', 'show')->name('answers.show');
                Route::put('/', 'update')->name('answers.update');
                Route::delete('/', 'destroy')->name('answers.destroy');

                // Routes for replies
                Route::get('replies', 'getReplies')->name('answers.replies');
                Route::post('replies', 'storeReply')->name('answers.storeReply');
            });
        });
    });
});
