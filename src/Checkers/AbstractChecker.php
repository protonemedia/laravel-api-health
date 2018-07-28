<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;

abstract class AbstractChecker implements Checker, CheckerIsScheduled, CheckerSendsNotifications
{
    /**
     * Event object that is used to manages the frequency.
     *
     * @var \Illuminate\Console\Scheduling\Event
     */
    private $event;

    /**
     * Number of minutes until send the failed notification again.
     *
     * @var int
     */
    protected $resendFailedNotificationAfterMinutes;

    /**
     * Number of times the checker must fail in succession before
     * the first notification is send.
     *
     * @var int
     */
    protected $onlySendFailedNotificationAfterSuccessiveFailures;

    /**
     * Class name of the failed notification.
     *
     * @var string
     */
    protected $failedNotificationClass;

    /**
     * Class name of the recovered notification.
     *
     * @var string
     */
    protected $recoveredNotificationClass;

    /**
     * Number of minutes until send the failed notification again.
     *
     * @return int
     */
    public function resendFailedNotificationAfterMinutes(): int
    {
        return $this->resendFailedNotificationAfterMinutes ?: config('api-health.notifications.resend_failed_notification_after_minutes');
    }

    /**
     * Number of times the checker must fail in succession before
     * the first notification is send.
     *
     * @return int
     */
    public function onlySendFailedNotificationAfterSuccessiveFailures(): int
    {
        return $this->onlySendFailedNotificationAfterSuccessiveFailures ?: config('api-health.notifications.only_send_failed_notification_after_successive_failures');
    }

    /**
     * Class name of the failed notification.
     *
     * @return string
     */
    public function failedNotificationClass(): string
    {
        return $this->failedNotificationClass ?: config('api-health.notifications.default_failed_notification');
    }

    /**
     * Class name of the recovered notification.
     *
     * @return string
     */
    public function recoveredNotificationClass(): string
    {
        return $this->recoveredNotificationClass ?: config('api-health.notifications.default_recovered_notification');
    }

    /**
     * Returns the EventMutex bound to the container or creates a new instance.
     *
     * @return \Illuminate\Console\Scheduling\EventMutex
     */
    private function eventMutex(): EventMutex
    {
        if (app()->bound(EventMutex::class)) {
            return app()->make(EventMutex::class);
        }

        return app()->make(CacheEventMutex::class);
    }

    /**
     * Returns the Event that is used to manages the frequency.
     *
     * @return \Illuminate\Console\Scheduling\Event
     */
    private function event(): Event
    {
        if (!$this->event) {
            $this->event = new Event($this->eventMutex(), '');
        }

        return $this->event;
    }

    /**
     * Determine if the checker is due to run based on the current date.
     *
     * @return bool
     */
    public function isDue(): bool
    {
        $this->schedule($this->event());

        return $this->event->isDue(app());
    }

    /**
     * Defines the checker's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Event  $event
     * @return void
     */
    public function schedule(Event $event)
    {
        $event->everyMinute();
    }
}
