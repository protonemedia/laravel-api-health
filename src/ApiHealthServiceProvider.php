<?php

namespace ProtoneMedia\ApiHealth;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Pbmedia\ApiHealth\ApiHealthChecker;
use Pbmedia\ApiHealth\Console\Check;
use Pbmedia\ApiHealth\Console\MakeChecker;
use Pbmedia\ApiHealth\Console\MakeHttpChecker;
use Pbmedia\ApiHealth\Console\MakeSslCertificateChecker;
use Pbmedia\ApiHealth\Console\RunCheckers;
use Pbmedia\ApiHealth\Events\CheckerHasFailed;
use Pbmedia\ApiHealth\Events\CheckerHasRecovered;
use Pbmedia\ApiHealth\Events\CheckerIsStillFailing;
use Pbmedia\ApiHealth\Listeners\SendFailedNotification;
use Pbmedia\ApiHealth\Listeners\SendRecoveredNotification;

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

        Event::listen(CheckerHasFailed::class, SendFailedNotification::class);
        Event::listen(CheckerIsStillFailing::class, SendFailedNotification::class);
        Event::listen(CheckerHasRecovered::class, SendRecoveredNotification::class);
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
        $this->app->bind('command.make:http-checker', MakeHttpChecker::class);
        $this->app->bind('command.make:ssl-certificate-checker', MakeSslCertificateChecker::class);

        $this->commands([
            'command.api-health:check',
            'command.api-health:run-checkers',
            'command.make:checker',
            'command.make:http-checker',
            'command.make:ssl-certificate-checker',
        ]);

        $this->app->singleton('laravel-api-health', function ($app) {
            return $app->make(ApiHealthChecker::class);
        });
    }
}
