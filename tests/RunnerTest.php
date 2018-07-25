<?php

namespace Pbmedia\ApiHealth\Tests;

use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Checkers\CheckWasUnsuccessful;
use Pbmedia\ApiHealth\Runner;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\PassingChecker;

class RunnerTest extends TestCase
{
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
        $runner = app(Runner::class)->handle();

        $this->assertCount(1, $passes = $runner->passes());
        $this->assertEquals(PassingChecker::class, $passes->first());

        $this->assertCount(1, $failed = $runner->failed());
        $this->assertInstanceOf(CheckWasUnsuccessful::class, $failed->first());
        $this->assertEquals(FailingChecker::class, $failed->keys()->first());
    }
}
