<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Services\FinancialService;
use App\Services\DoctorWalletService;
use App\Services\AuditLogService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(FinancialService::class);
        $this->app->singleton(DoctorWalletService::class);
        $this->app->singleton(AuditLogService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        
        // Register middleware aliases
        if ($this->app->bound('router')) {
            $router = $this->app['router'];
            $router->aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
            $router->aliasMiddleware('doctor', \App\Http\Middleware\DoctorMiddleware::class);
            $router->aliasMiddleware('representative', \App\Http\Middleware\RepresentativeMiddleware::class);
            $router->aliasMiddleware('request.id', \App\Http\Middleware\RequestIdMiddleware::class);
            $router->aliasMiddleware('admin.security', \App\Http\Middleware\AdminSecurityMiddleware::class);
            $router->aliasMiddleware('force.json', \App\Http\Middleware\ForceJsonResponse::class);
        }
    }

    /**
     * Configure rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default rate limit
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('app.rate_limit_per_minute', 60))->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limit for authentication routes
        RateLimiter::for('auth', function (Request $request) {
            $key = $request->ip();
            
            // Stricter limit if we detect rapid attempts
            $attempts = RateLimiter::attempts("auth:{$key}");
            if ($attempts > 10) {
                return Limit::perMinute(5)->by($key)->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many authentication attempts. Please try again later.',
                        'retry_after' => RateLimiter::availableIn(request()->ip())
                    ], 429);
                });
            }
            
            return Limit::perMinute(10)->by($key)->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many authentication attempts. Please try again later.',
                    'retry_after' => RateLimiter::availableIn(request()->ip())
                ], 429);
            });
        });

        // Rate limit for registration
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many registration attempts. Please try again later.',
                    'retry_after' => RateLimiter::availableIn(request()->ip())
                ], 429);
            });
        });

        // Rate limit for OTP requests
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many OTP requests. Please try again later.',
                    'retry_after' => RateLimiter::availableIn(request()->ip())
                ], 429);
            });
        });

        // Rate limit for password reset
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many password reset attempts. Please try again later.',
                    'retry_after' => RateLimiter::availableIn(request()->ip())
                ], 429);
            });
        });

        // Rate limit for file uploads
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many upload requests. Please try again later.'
                ], 429);
            });
        });

        // Admin API rate limit
        RateLimiter::for('admin-api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Order creation rate limit
        RateLimiter::for('orders', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many order requests. Please try again later.'
                ], 429);
            });
        });
    }
}
