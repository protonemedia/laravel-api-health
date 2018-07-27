<?php

namespace Pbmedia\ApiHealth\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Orchestra\Testbench\TestCase;
use Pbmedia\ApiHealth\Notifications\CheckerHasFailed as CheckerHasFailedNotification;
use Pbmedia\ApiHealth\Notifications\CheckerHasRecovered;
use Pbmedia\ApiHealth\Runner;
use Pbmedia\ApiHealth\Storage\CheckerState;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingAtEvenTimesChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingAtOddTimesChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\FailingChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\NotificationlessChecker;
use Pbmedia\ApiHealth\Tests\TestCheckers\PassingChecker;

class NotificationTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('api-health.checkers', [
            FailingChecker::class,
            PassingChecker::class,
        ]);

        $app['config']->set('api-health.cache_driver', 'array');
        $app['config']->set('api-health.notifications.via', ['mail']);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Pbmedia\ApiHealth\LaravelServiceProvider::class,
        ];
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
    public function it_doesnt_notify_whenever_the_checker_does_not_implement_the_notification_interface()
    {
        Notification::fake();

        config()->set('api-health.checkers', [
            NotificationlessChecker::class,
        ]);

        $runner = app(Runner::class)->handle();

        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class
        );
    }

    /** @test */
    public function it_can_notify_whenever_a_checker_fails()
    {
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

    /** @test */
    public function it_notifies_again_after_60_minutes()
    {
        Notification::fake();

        Carbon::setTestNow('2018-07-01 10:00:00');

        $runner = app(Runner::class)->handle();

        Notification::assertSentToTimes(
            $notifiable = app(config('api-health.notifications.notifiable')),
            $checker = CheckerHasFailedNotification::class,
            1
        );

        Carbon::setTestNow('2018-07-01 10:59:00');
        $runner = app(Runner::class)->handle();
        Notification::assertSentToTimes($notifiable, $checker, 1);

        Carbon::setTestNow('2018-07-01 11:00:00');
        $runner = app(Runner::class)->handle();
        Notification::assertSentToTimes($notifiable, $checker, 2);

        Carbon::setTestNow('2018-07-01 11:59:59');
        $runner = app(Runner::class)->handle();
        Notification::assertSentToTimes($notifiable, $checker, 2);

        Carbon::setTestNow('2018-07-01 12:00:00');
        $runner = app(Runner::class)->handle();
        Notification::assertSentToTimes($notifiable, $checker, 3);
    }

    /** @test */
    public function it_only_notifies_once()
    {
        Notification::fake();

        app(Runner::class)->handle();
        app(Runner::class)->handle();

        Notification::assertSentToTimes(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            1
        );
    }

    /** @test */
    public function it_notifies_if_it_recovers_after_it_has_failed()
    {
        Notification::fake();

        config()->set('api-health.checkers', [
            FailingAtOddTimesChecker::class,
        ]);

        //

        $runner = app(Runner::class)->handle();

        $state = new CheckerState(FailingAtOddTimesChecker::create());
        $this->assertFalse($state->isFailed());

        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasRecovered::class
        );

        $runner->handle();
        $this->assertTrue($state->isFailed());

        $runner->handle();
        $this->assertFalse($state->isFailed());

        Notification::assertSentToTimes(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            1
        );

        Notification::assertSentToTimes(
            app(config('api-health.notifications.notifiable')),
            CheckerHasRecovered::class,
            1
        );

        Notification::assertSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasRecovered::class,
            function ($notification) {
                return $notification->exceptionMessage === 'TestChecker fails!';
            }
        );
    }

    /** @test */
    public function it_notifies_again_if_it_fails_after_it_has_recovered()
    {
        Notification::fake();

        config()->set('api-health.checkers', [
            FailingAtEvenTimesChecker::class,
        ]);

        //

        $state  = new CheckerState(FailingAtEvenTimesChecker::create());
        $runner = app(Runner::class);

        $runner->handle();
        $this->assertTrue($state->isFailed());

        $runner->handle();
        $this->assertFalse($state->isFailed());

        $runner->handle();
        $this->assertTrue($state->isFailed());

        Notification::assertSentToTimes(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class,
            2
        );
    }
}
