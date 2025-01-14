<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/auth/otp', [AuthController::class, 'handleOtp']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('user', [UserController::class, 'user']);
    Route::post('user', [UserController::class, 'update']);
});
