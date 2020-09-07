<?php

namespace ProtoneMedia\ApiHealth\Tests;

use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;
use ProtoneMedia\ApiHealth\Events\CheckerHasFailed;
use ProtoneMedia\ApiHealth\Events\CheckerHasRecovered;
use ProtoneMedia\ApiHealth\Events\CheckerIsStillFailing;
use ProtoneMedia\ApiHealth\Runner;
use ProtoneMedia\ApiHealth\Tests\TestCheckers\FailingAtEvenTimesChecker;
use ProtoneMedia\ApiHealth\Tests\TestCheckers\FailingChecker;

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
            \ProtoneMedia\ApiHealth\ApiHealthServiceProvider::class,
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
