<?php

namespace Pbmedia\ApiHealth\Tests;

use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Events\CheckerHasFailed;
use Pbmedia\ApiHealth\Events\CheckerHasRecovered;
use Pbmedia\ApiHealth\Events\CheckerIsStillFailing;
use Pbmedia\ApiHealth\Runner;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingAtEvenTimesChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingChecker;

class EventTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [FailingChecker::class]);
        $app['config']->set('api-health.cache_driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\ApiHealthServiceProvider::class,
        ];
    }

    /** @test */
    public function it_fires_one_event_whenever_a_checker_fails()
    {
        Event::fake();

        Runner::fromConfig()->handle();
        Runner::fromConfig()->handle();

        Event::assertDispatchedTimes(CheckerHasFailed::class, 1);
    }

    /** @test */
    public function it_fires_an_event_if_the_checker_still_fails()
    {
        Event::fake();

        Runner::fromConfig()->handle();
        Runner::fromConfig()->handle();

        Event::assertDispatchedTimes(CheckerHasFailed::class, 1);
        Event::assertDispatchedTimes(CheckerIsStillFailing::class, 1);
    }

    /** @test */
    public function it_fires_an_event_if_the_checker_recovers()
    {
        Event::fake();

        config()->set('api-health.checkers', [FailingAtEvenTimesChecker::class]);

        Runner::fromConfig()->handle();
        Runner::fromConfig()->handle();

        Event::assertDispatchedTimes(CheckerHasFailed::class, 1);
        Event::assertDispatchedTimes(CheckerHasRecovered::class, 1);
    }
}
