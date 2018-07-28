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
            \Pbmedia\ApiHealth\ApiHealthServiceProvider::class,
        ];
    }

    private function assertNotSent()
    {
        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasFailedNotification::class
        );
    }

    private function assertSentFailedTimes($times)
    {
        Notification::assertSentToTimes(
            $notifiable = app(config('api-health.notifications.notifiable')),
            $checker = CheckerHasFailedNotification::class,
            $times
        );
    }

    /** @test */
    public function it_doesnt_notify_whenever_the_via_config_is_empty()
    {
        config()->set('api-health.notifications.via', []);

        Notification::fake();

        app(Runner::class)->handle();

        $this->assertNotSent();
    }

    /** @test */
    public function it_doesnt_notify_whenever_the_checker_does_not_implement_the_notification_interface()
    {
        Notification::fake();

        config()->set('api-health.checkers', [NotificationlessChecker::class]);

        app(Runner::class)->handle();

        $this->assertNotSent();
    }

    /** @test */
    public function it_can_notify_whenever_a_checker_fails()
    {
        Notification::fake();

        app(Runner::class)->handle();

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
        app(Runner::class)->handle();
        $this->assertSentFailedTimes(1);

        Carbon::setTestNow('2018-07-01 10:59:00');
        app(Runner::class)->handle();
        $this->assertSentFailedTimes(1);

        Carbon::setTestNow('2018-07-01 11:00:00');
        app(Runner::class)->handle();
        $this->assertSentFailedTimes(2);

        Carbon::setTestNow('2018-07-01 11:59:59');
        app(Runner::class)->handle();
        $this->assertSentFailedTimes(2);

        Carbon::setTestNow('2018-07-01 12:00:00');
        app(Runner::class)->handle();
        $this->assertSentFailedTimes(3);
    }

    /** @test */
    public function it_only_notifies_once()
    {
        Notification::fake();

        app(Runner::class)->handle();
        app(Runner::class)->handle();

        $this->assertSentFailedTimes(1);
    }

    /** @test */
    public function it_only_notifies_once_if_the_resend_minutes_is_set_to_zero()
    {
        Notification::fake();

        config()->set('api-health.notifications.resend_failed_notification_after_minutes', 0);

        app(Runner::class)->handle();
        app(Runner::class)->handle();

        $this->assertSentFailedTimes(1);
    }

    /** @test */
    public function it_can_postpone_a_failed_notification_with_a_threshold()
    {
        Notification::fake();

        config()->set('api-health.notifications.only_send_failed_notification_after_successive_failures', 3);

        app(Runner::class)->handle();
        $this->assertNotSent();

        app(Runner::class)->handle();
        $this->assertNotSent();

        app(Runner::class)->handle();
        $this->assertSentFailedTimes(1);

        app(Runner::class)->handle();
        $this->assertSentFailedTimes(1);
    }

    /** @test */
    public function it_notifies_if_it_recovers_after_it_has_failed()
    {
        Notification::fake();

        config()->set('api-health.checkers', [FailingAtOddTimesChecker::class]);

        $runner = app(Runner::class)->handle();

        $state = new CheckerState(FailingAtOddTimesChecker::create());
        $this->assertFalse($state->isFailing());

        Notification::assertNotSentTo(
            app(config('api-health.notifications.notifiable')),
            CheckerHasRecovered::class
        );

        $runner->handle();
        $this->assertTrue($state->isFailing());

        $runner->handle();
        $this->assertFalse($state->isFailing());
        $this->assertSentFailedTimes(1);

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

        config()->set('api-health.checkers', [FailingAtEvenTimesChecker::class]);

        $state  = new CheckerState(FailingAtEvenTimesChecker::create());
        $runner = app(Runner::class);

        $runner->handle();
        $this->assertTrue($state->isFailing());

        $runner->handle();
        $this->assertFalse($state->isFailing());

        $runner->handle();
        $this->assertTrue($state->isFailing());

        $this->assertSentFailedTimes(2);
    }
}
