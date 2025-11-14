<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Broadcasting authentication routes
Broadcast::routes(['middleware' => ['auth:api']]);

//Any Route For Frontend
Route::any('{any}', [HomeController::class, 'index'])->where('any', '.*');
