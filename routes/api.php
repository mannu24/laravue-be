<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/auth/otp', [AuthController::class, 'handleOtp']);
Route::post('feed', [PostController::class, 'index']); // Use post_code as the parameter

Route::middleware(['auth:api'])->group(function () {
    Route::get('user', [UserController::class, 'user']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::post('user', [UserController::class, 'update']);

    Route::get('posts/{post_code}', [PostController::class, 'show']); // Use post_code as the parameter
    Route::apiResource('posts', PostController::class)->except(['show','index']);
});
