<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

//Any Route For Frontend
Route::any('{any}', [HomeController::class, 'index'])->where('any', '.*');