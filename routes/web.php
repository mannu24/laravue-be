<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\v1\Admin\BlogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Route::prefix('admin')->group(function () {
    Auth::routes();
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Blog Routes
    Route::resource('blogs', BlogController::class);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Any Route For Frontend
// Route::any('{any}', [HomeController::class, 'index'])->where('any', '.*');
