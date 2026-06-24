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
        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        $key = fn (Request $request): string => $request->user()
            ? 'user:'.$request->user()->id
            : 'ip:'.$request->ip();

        RateLimiter::for('api-public', fn (Request $request) => Limit::perMinute(120)->by($key($request)));
        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(10)->by($key($request).'|'.$request->input('email', 'guest')));
        RateLimiter::for('api-customer', fn (Request $request) => Limit::perMinute(120)->by($key($request)));
        RateLimiter::for('api-rider', fn (Request $request) => Limit::perMinute(120)->by($key($request)));
        RateLimiter::for('api-admin', fn (Request $request) => Limit::perMinute(90)->by($key($request)));
        RateLimiter::for('api-checkout', fn (Request $request) => Limit::perMinute(8)->by($key($request)));
        RateLimiter::for('api-status-update', fn (Request $request) => Limit::perMinute(20)->by($key($request)));
        RateLimiter::for('api-upload', fn (Request $request) => Limit::perMinute(20)->by($key($request)));
    }
}
