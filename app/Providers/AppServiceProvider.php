<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FinancialService;
use App\Services\DoctorWalletService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services
        $this->app->singleton(FinancialService::class);
        $this->app->singleton(DoctorWalletService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register middleware aliases
        // Use the router instance directly to avoid facade dependency issues
        if ($this->app->bound('router')) {
            $router = $this->app['router'];
            $router->aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
            $router->aliasMiddleware('doctor', \App\Http\Middleware\DoctorMiddleware::class);
            $router->aliasMiddleware('representative', \App\Http\Middleware\RepresentativeMiddleware::class);
        }
    }
}
