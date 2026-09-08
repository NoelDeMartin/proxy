<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $allowedOrigins = config('cors.allowed_origins');

        if ($this->app->isProduction() && is_array($allowedOrigins) && in_array('*', $allowedOrigins, true)) {
            throw new \RuntimeException('Wildcard origins are not allowed in production, please configure CORS_ORIGINS in your .env file.');
        }

        RateLimiter::for('api', function (Request $request) {
            $limit = Limit::perMinutes(10, config()->integer('rate_limiting.requests'));

            return config()->boolean('rate_limiting.global')
                ? $limit
                : $limit->by($request->ip());
        });
    }
}
