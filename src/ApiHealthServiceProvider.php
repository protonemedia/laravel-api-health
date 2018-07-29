<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\ServiceProvider;
use Pbmedia\ApiHealth\ApiHealthChecker;
use Pbmedia\ApiHealth\Console\Check;
use Pbmedia\ApiHealth\Console\MakeChecker;
use Pbmedia\ApiHealth\Console\MakeHttpGetChecker;
use Pbmedia\ApiHealth\Console\MakeSslCertificateChecker;
use Pbmedia\ApiHealth\Console\RunCheckers;

class ApiHealthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/api-health.php' => config_path('api-health.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/api-health'),
        ]);

        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang/', 'api-health'
        );
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api-health.php', 'api-health');

        $this->app->bind('command.api-health:check', Check::class);
        $this->app->bind('command.api-health:run-checkers', RunCheckers::class);
        $this->app->bind('command.make:checker', MakeChecker::class);
        $this->app->bind('command.make:http-get-checker', MakeHttpGetChecker::class);
        $this->app->bind('command.make:ssl-certificate-checker', MakeSslCertificateChecker::class);

        $this->commands([
            'command.api-health:check',
            'command.api-health:run-checkers',
            'command.make:checker',
            'command.make:http-get-checker',
            'command.make:ssl-certificate-checker',
        ]);

        $this->app->singleton('laravel-api-health', function ($app) {
            return $app->make(ApiHealthChecker::class);
        });
    }
}
