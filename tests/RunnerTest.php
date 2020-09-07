<?php

namespace ProtoneMedia\ApiHealth\Tests;

use Orchestra\Testbench\TestCase;
use ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed;
use ProtoneMedia\ApiHealth\Runner;
use ProtoneMedia\ApiHealth\Tests\TestCheckers\FailingChecker;
use ProtoneMedia\ApiHealth\Tests\TestCheckers\PassingChecker;

class RunnerTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [
            FailingChecker::class,
            PassingChecker::class,
        ]);

        $app['config']->set('api-health.cache_driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            \ProtoneMedia\ApiHealth\ApiHealthServiceProvider::class,
        ];
    }

    /** @test */
    public function it_runs_the_configured_checkers_and_returns_the_failed_and_passed_checkers()
    {
        $runner = Runner::fromConfig();

        $this->assertCount(1, $passes = $runner->passes());
        $this->assertInstanceOf(PassingChecker::class, $passes->first()->getChecker());

        $this->assertCount(1, $failed = $runner->failed());
        $this->assertInstanceOf(FailingChecker::class, $failed->first()->getChecker());
        $this->assertInstanceOf(CheckerHasFailed::class, $failed->first()->getException());
    }
}
