<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override the broadcast manager to conditionally handle ShouldBroadcast events
        $this->app->singleton(BroadcastManager::class, function ($app) {
            $manager = new BroadcastManager($app);
            
            // In local environment, intercept ShouldBroadcast events and broadcast immediately
            if (app()->environment('local')) {
                // Use a custom queue connection that processes immediately
                config(['queue.connections.broadcast' => [
                    'driver' => 'sync',
                ]]);
            }
            
            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

