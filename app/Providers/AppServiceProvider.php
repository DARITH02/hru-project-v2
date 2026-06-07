<?php

namespace App\Providers;

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
        // 🛡️ SECURITY: Define Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });
        
        \Illuminate\Support\Facades\RateLimiter::for('activity', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('teacher-qr', function (\Illuminate\Http\Request $request) {
            $code = strtoupper((string) $request->input('teacher_identifier', ''));

            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip() . '|' . $code);
        });
    }
}
