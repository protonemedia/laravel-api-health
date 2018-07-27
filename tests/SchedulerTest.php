<?php

namespace Pbmedia\ApiHealth\Tests;

use Illuminate\Support\Carbon;
use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Runner;
use Pbmedia\ApiHealth\Tests\TestCheckers\EveryFiveMinutesChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\EveryMinuteChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\SchedulessChecker;

class SchedulerTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [
            EveryMinuteChecker::class,
            EveryFiveMinutesChecker::class,
        ]);

        $app['config']->set('api-health.cache_driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\LaravelServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_determinate_if_a_checker_should_run()
    {
        $runner = app(Runner::class);

        Carbon::setTestNow('2018-07-07 10:00:00');
        $this->assertCount(2, $runner->handle()->passes());

        Carbon::setTestNow('2018-07-07 10:03:00');
        $this->assertCount(1, $runner->handle()->passes());

        Carbon::setTestNow('2018-07-07 10:05:00');
        $this->assertCount(2, $runner->handle()->passes());
    }

    /** @test */
    public function it_can_ignore_the_scheduling()
    {
        $runner = app(Runner::class)->ignoreScheduling();

        Carbon::setTestNow('2018-07-07 10:00:00');
        $this->assertCount(2, $runner->handle()->passes());

        Carbon::setTestNow('2018-07-07 10:03:00');
        $this->assertCount(2, $runner->handle()->passes());
    }

    /** @test */
    public function it_always_runs_a_checker_that_not_implements_the_scheduler_interface()
    {
        config()->set('api-health.checkers', [
            SchedulessChecker::class,
        ]);

        $runner = app(Runner::class);

        $this->assertCount(1, $runner->handle()->passes());
    }
}
