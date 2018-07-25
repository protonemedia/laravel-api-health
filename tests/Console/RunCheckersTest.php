<?php

namespace Pbmedia\ApiHealth\Tests\Console;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
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
    }

    /** @test */
    public function it_runs_the_configured_checkers_and_retuns_the_caught_exceptions()
    {
        Artisan::call('api-health:run-checkers');

        $this->assertMatchesSnapshot(Artisan::output());
    }
}
