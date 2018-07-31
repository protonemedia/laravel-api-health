<?php

namespace Pbmedia\ApiHealth\Tests;

use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Jobs\RetryChecker;
use Pbmedia\ApiHealth\Runner;
use Pbmedia\ApiHealth\Storage\CheckerState;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingCheckerWithJobCallback;

class AllowedRetriesTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [FailingChecker::class]);
        $app['config']->set('api-health.cache_driver', 'array');
    }

    /** @test */
    public function it_allows_for_retries()
    {
        config()->set('api-health.allowed_retries', 2);
        config()->set('api-health.default_retry_checker_job', null);

        $runner = app(Runner::class);
        $this->assertCount(1, $runner->passes());
        $this->assertCount(0, $runner->failed());

        $runner = app(Runner::class);
        $this->assertCount(1, $runner->passes());
        $this->assertCount(0, $runner->failed());

        $runner = app(Runner::class);
        $this->assertCount(0, $runner->passes());
        $this->assertCount(1, $runner->failed());
    }

    /** @test */
    public function it_keeps_failing_once_the_state_is_failed()
    {
        config()->set('api-health.allowed_retries', 1);
        config()->set('api-health.default_retry_checker_job', null);

        $runner = app(Runner::class);
        $this->assertCount(1, $runner->passes());
        $this->assertCount(0, $runner->failed());

        $runner = app(Runner::class);
        $this->assertCount(0, $runner->passes());
        $this->assertCount(1, $runner->failed());

        $runner = app(Runner::class);
        $this->assertCount(0, $runner->passes());
        $this->assertCount(1, $runner->failed());
    }

    /** @test */
    public function it_has_a_default_job_that_retries_the_checker()
    {
        config()->set('api-health.allowed_retries', 1);
        config()->set('api-health.default_retry_checker_job', RetryChecker::class);

        app(Runner::class)->handle();

        $this->assertTrue(CheckerState::make(FailingChecker::class)->isFailing());
    }

    /** @test */
    public function it_has_a_callback_method_for_the_retry_job()
    {
        config()->set('api-health.allowed_retries', 1);
        config()->set('api-health.default_retry_checker_job', RetryChecker::class);
        config()->set('api-health.checkers', [FailingCheckerWithJobCallback::class]);

        $this->assertNull(FailingCheckerWithJobCallback::$job);

        app(Runner::class)->handle();

        $this->assertNotNull($job = FailingCheckerWithJobCallback::$job);
        $this->assertEquals(10, $job->delay);
    }
}
