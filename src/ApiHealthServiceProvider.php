<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\ServiceProvider;
use Pbmedia\ApiHealth\ApiHealthChecker;
use Pbmedia\ApiHealth\Console\MakeChecker;
use Pbmedia\ApiHealth\Console\MakeHttpGetChecker;
use Pbmedia\ApiHealth\Console\RunCheckers;

class ApiHealthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/api-health.php' => config_path('api-health.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/api-health'),
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api-health.php', 'api-health');

        $this->app->bind('command.api-health:run-checkers', RunCheckers::class);
        $this->app->bind('command.make:checker', MakeChecker::class);
        $this->app->bind('command.make:http-get-checker', MakeHttpGetChecker::class);

        $this->commands([
            'command.api-health:run-checkers',
            'command.make:checker',
            'command.make:http-get-checker',
        ]);

        $this->app->singleton('laravel-api-health', function ($app) {
            return $app->make(ApiHealthChecker::class);
        });
    }
}
