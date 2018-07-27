<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;

abstract class AbstractChecker implements Checker, CheckerIsScheduled, CheckerSendsNotifications
{
    private $event;

    protected $resendFailedNotificationAfterMinutes;

    protected $failedNotificationClass;

    protected $recoveredNotificationClass;

    private function eventMutex(): EventMutex
    {
        if (app()->bound(EventMutex::class)) {
            return app()->make(EventMutex::class);
        }

        return app()->make(CacheEventMutex::class);
    }

    private function event()
    {
        if (!$this->event) {
            $this->event = new Event($this->eventMutex(), '');
        }

        return $this->event;
    }

    public function resendFailedNotificationAfterMinutes(): int
    {
        return $this->resendFailedNotificationAfterMinutes ?: config('api-health.notifications.resend_failed_notification_after_minutes');
    }

    public function failedNotificationClass(): string
    {
        return $this->failedNotificationClass ?: config('api-health.notifications.default_failed_notification');
    }

    public function recoveredNotificationClass(): string
    {
        return $this->recoveredNotificationClass ?: config('api-health.notifications.default_recovered_notification');
    }

    public function isDue(): bool
    {
        $this->schedule($this->event());

        return $this->event->isDue(app());
    }

    public function schedule(Event $event)
    {
        $event->everyMinute();
    }
}
