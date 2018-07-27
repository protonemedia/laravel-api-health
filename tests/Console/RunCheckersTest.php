<?php

namespace Pbmedia\ApiHealth\Tests\Console;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Tests\TestCheckers\EveryFiveMinutesChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\EveryMinuteChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\PassingChecker;
use Spatie\Snapshots\MatchesSnapshots;

class RunCheckersTest extends TestCase
{
    use MatchesSnapshots;

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\LaravelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [
            FailingChecker::class,
            PassingChecker::class,
        ]);

        $app['config']->set('api-health.cache_driver', 'array');
    }

    /** @test */
    public function it_runs_the_configured_checkers_and_retuns_the_caught_exceptions()
    {
        Artisan::call('api-health:run-checkers');

        $this->assertMatchesSnapshot(Artisan::output());
    }

    /** @test */
    public function it_has_a_force_option_to_ignore_the_scheduling()
    {
        Carbon::setTestNow('2018-07-01 10:01:00');

        config()->set('api-health.checkers', [
            EveryMinuteChecker::class,
            EveryFiveMinutesChecker::class,
        ]);

        Artisan::call('api-health:run-checkers');

        $this->assertMatchesSnapshot(Artisan::output());

        Artisan::call('api-health:run-checkers', ['--force' => true]);

        $this->assertMatchesSnapshot(Artisan::output());
    }
}
