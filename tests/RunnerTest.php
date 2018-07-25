<?php

namespace Pbmedia\ApiHealth\Tests;

use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use Pbmedia\ApiHealth\Notifications\CheckerHasFailed as CheckerHasFailedNotification;
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

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\LaravelServiceProvider::class,
        ];
    }

    /** @test */
    public function it_runs_the_configured_checkers_and_returns_the_failed_and_passed_checkers()
    {
        $runner = app(Runner::class)->handle();

        $this->assertCount(1, $passes = $runner->passes());
        $this->assertInstanceOf(PassingChecker::class, $passes->first());

        $this->assertCount(1, $failed = $runner->failed());
        $this->assertInstanceOf(CheckerHasFailed::class, $failed->first());
        $this->assertInstanceOf(FailingChecker::class, $failed->first()->getChecker());
    }

    /** @test */
    public function it_doesnt_notify_whenever_the_via_config_is_empty()
    {
        config()->set('api-health.notifications.via', []);

        Notification::fake();

        $runner = app(Runner::class)->handle();

        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class
        );
    }

    /** @test */
    public function it_can_notify_whenever_a_checker_fails()
    {
        config()->set('api-health.notifications.via', ['mail']);

        Notification::fake();

        $runner = app(Runner::class)->handle();

        Notification::assertSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            function ($notification, $channels) {
                return $channels === ['mail'];
            }
        );
    }
}
