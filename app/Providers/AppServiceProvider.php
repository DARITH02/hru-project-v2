<?php

namespace App\Providers;

use App\Filesystem\GoogleDriveAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

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
        Storage::extend('google', function ($app, array $config) {
            $adapter = new GoogleDriveAdapter($app->make(\App\Services\GoogleDriveService::class));

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });

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
