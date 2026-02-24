<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Register Core Service Providers Early
|--------------------------------------------------------------------------
|
| Register core service providers early to ensure all bindings and commands
| are available before other service providers try to use them.
|
*/

$app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(\Illuminate\Cache\CacheServiceProvider::class);
$app->register(\Illuminate\Hashing\HashServiceProvider::class);
$app->register(\Illuminate\Queue\QueueServiceProvider::class);
$app->register(\Illuminate\View\ViewServiceProvider::class);
$app->register(\Illuminate\Routing\RoutingServiceProvider::class);
$app->register(\Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class);
$app->register(\Illuminate\Database\DatabaseServiceProvider::class);
$app->register(\Illuminate\Cookie\CookieServiceProvider::class);
$app->register(\Illuminate\Session\SessionServiceProvider::class);
$app->register(\Illuminate\Auth\AuthServiceProvider::class);
$app->register(\Illuminate\Translation\TranslationServiceProvider::class);
$app->register(\Illuminate\Validation\ValidationServiceProvider::class);

// Register encrypter manually (will be resolved when needed, after config is loaded)
$app->singleton('encrypter', function ($app) {
    // Try to get config, if not available yet, use defaults
    try {
        if ($app->bound('config')) {
            $appConfig = $app->make('config')->get('app');
            $key = $appConfig['key'] ?? '';
            $cipher = $appConfig['cipher'] ?? 'AES-256-CBC';
        } else {
            // Config not loaded yet, use environment or defaults
            $key = $_ENV['APP_KEY'] ?? '';
            $cipher = $_ENV['APP_CIPHER'] ?? 'AES-256-CBC';
        }
        
        if (empty($key)) {
            // If no key, create a temporary one (will be replaced by key:generate)
            $key = 'base64:' . base64_encode(random_bytes(32));
        }
        
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        
        return new \Illuminate\Encryption\Encrypter($key, $cipher);
    } catch (\Exception $e) {
        // Fallback if anything goes wrong
        $key = $_ENV['APP_KEY'] ?? 'base64:' . base64_encode(random_bytes(32));
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return new \Illuminate\Encryption\Encrypter($key, 'AES-256-CBC');
    }
});

// Also bind the contract
$app->bind(
    \Illuminate\Contracts\Encryption\Encrypter::class,
    'encrypter'
);

$app->bind(
    \Illuminate\Contracts\Encryption\StringEncrypter::class,
    'encrypter'
);

// Register application service providers from bootstrap/providers.php
if (file_exists($providersPath = __DIR__.'/providers.php')) {
    $providers = require $providersPath;
    foreach ($providers as $provider) {
        $app->register($provider);
    }
}

// Register MaintenanceMode binding manually (use file driver directly to avoid config dependency)
// This must be registered before any middleware tries to use it
$app->singleton(\Illuminate\Foundation\MaintenanceModeManager::class, function ($app) {
    return new \Illuminate\Foundation\MaintenanceModeManager($app);
});

$app->bind(
    \Illuminate\Contracts\Foundation\MaintenanceMode::class,
    function ($app) {
        $manager = $app->make(\Illuminate\Foundation\MaintenanceModeManager::class);
        // Try to get driver from config, fallback to 'file'
        try {
            if ($app->bound('config')) {
                $driver = $app->make('config')->get('app.maintenance.driver', 'file');
            } else {
                $driver = 'file';
            }
        } catch (\Exception $e) {
            $driver = 'file';
        }
        return $manager->driver($driver);
    }
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;

