<?php

use App\Console\Commands\ScrapeStackOverflow;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        ScrapeStackOverflow::class,
        \App\Console\Commands\ResetDailyTasks::class,
        \App\Console\Commands\ResetWeeklyTasks::class,
        \App\Console\Commands\CheckUserStreaks::class,
        \App\Console\Commands\TestGamificationCron::class,
        \App\Console\Commands\ExpirePortfolioSubscriptions::class,
        \App\Console\Commands\SendPortfolioExpiryReminders::class,
        \App\Console\Commands\VerifyPortfolioDomains::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Portfolio subdomain/custom domain detection — runs on every request
        $middleware->prepend(\App\Http\Middleware\PortfolioDomainMiddleware::class);

        $middleware->preventRequestForgery(except: [
            'api/*'
        ]);

        // Register named middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.web' => \App\Http\Middleware\AdminWebMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
