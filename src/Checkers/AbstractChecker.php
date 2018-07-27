<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;

abstract class AbstractChecker implements Checker, CheckerIsScheduled, CheckerSendsNotifications
{
    private $event;

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

    public function getFailedNotificationClass(): string
    {
        return $this->failedNotificationClass ?: config('api-health.notifications.default_failed_notification');
    }

    public function getRecoveredNotificationClass(): string
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
