<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Portfolio Preview (web route — uses Passport token from query param)
Route::get('/portfolio-preview', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    if (!$token) {
        return response('Unauthorized', 401);
    }

    // Authenticate via Passport token
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $user = auth('api')->user();
    if (!$user) {
        return response('Unauthorized', 401);
    }

    $portfolio = \App\Models\Portfolio::where('user_id', $user->id)
        ->with(['socialLinks', 'skills', 'experiences', 'educations', 'projects', 'testimonials', 'customSections', 'user'])
        ->first();

    if (!$portfolio) {
        return response('No portfolio found', 404);
    }

    $templateSlug = $portfolio->template_slug ?? 'minimal';
    $viewName = "portfolio.templates.{$templateSlug}.index";
    if (!view()->exists($viewName)) {
        $viewName = 'portfolio.templates.minimal.index';
    }

    return view($viewName, [
        'portfolio' => $portfolio,
        'isGracePeriod' => false,
    ]);
})->name('portfolio.preview');

// Admin Routes (Blade — session auth)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware('admin.web')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/portfolios', [AdminDashboardController::class, 'portfolios'])->name('admin.portfolios');
        Route::post('/portfolios/{id}/toggle-publish', [AdminDashboardController::class, 'togglePublish'])->name('admin.portfolios.toggle');

        Route::get('/plans', [AdminDashboardController::class, 'plans'])->name('admin.plans');
        Route::put('/plans/{id}', [AdminDashboardController::class, 'updatePlan'])->name('admin.plans.update');

        Route::get('/coupons', [AdminDashboardController::class, 'coupons'])->name('admin.coupons');
        Route::post('/coupons', [AdminDashboardController::class, 'storeCoupon'])->name('admin.coupons.store');
        Route::post('/coupons/{id}/toggle', [AdminDashboardController::class, 'toggleCoupon'])->name('admin.coupons.toggle');

        Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('admin.orders');

        Route::get('/subscriptions', [AdminDashboardController::class, 'subscriptions'])->name('admin.subscriptions');
        Route::post('/subscriptions/{id}/refund', [AdminDashboardController::class, 'refundSubscription'])->name('admin.subscriptions.refund');

        Route::get('/templates', [AdminDashboardController::class, 'templates'])->name('admin.templates');
        Route::post('/templates/{id}/toggle', [AdminDashboardController::class, 'toggleTemplate'])->name('admin.templates.toggle');
    });
});

// Vue SPA catch-all (must be last)
Route::any('{any}', [HomeController::class, 'index'])->where('any', '.*');
