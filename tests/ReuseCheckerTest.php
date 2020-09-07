<?php

namespace ProtoneMedia\ApiHealth\Tests;

use Orchestra\Testbench\TestCase;
use ProtoneMedia\ApiHealth\Checkers\Executor;
use ProtoneMedia\ApiHealth\Storage\CheckerState;
use ProtoneMedia\ApiHealth\Tests\TestCheckers\BasedOnIdChecker;

class ReuseCheckerTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.cache_driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            \ProtoneMedia\ApiHealth\ApiHealthServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_have_two_different_states_for_the_same_checker()
    {
        $passingChecker = new BasedOnIdChecker(true);
        $failingChecker = new BasedOnIdChecker(false);

        $this->assertTrue(
            (new Executor($passingChecker))->passes()
        );

        $this->assertFalse(
            (new Executor($failingChecker))->passes()
        );

        $passingState = new CheckerState($passingChecker);
        $failingState = new CheckerState($failingChecker);

        $this->assertTrue($passingState->exists());
        $this->assertTrue($passingState->isPassing());

        $this->assertTrue($failingState->exists());
        $this->assertTrue($failingState->isFailing());
    }
}
