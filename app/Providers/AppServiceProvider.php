<?php

namespace App\Providers;

use App\Contracts\AuthServiceInterface;
use App\Services\AuthService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | API Rate Limiter
        |--------------------------------------------------------------------------
        |
        | All authenticated API routes using throttle:api are limited
        | to 120 requests per minute per authenticated user.
        |
        | If the request is not authenticated, the IP address is used.
        |
        */

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)
                ->by(
                    $request->user()?->id
                    ?? $request->ip()
                );
        });
    }
}

