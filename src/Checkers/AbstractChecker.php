<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;

abstract class AbstractChecker implements Checker, CheckerIsScheduled, CheckerSendsNotifications
{
    protected $event;

    protected $failedNotificationClass;

    private function event()
    {
        if (!$this->event) {
            $eventMutex = app()->bound(EventMutex::class) ? app()->make(EventMutex::class) : app()->make(CacheEventMutex::class);

            $this->event = new Event($eventMutex, '');
        }

        return $this->event;
    }

    public function getFailedNotificationClass(): string
    {
        return $this->failedNotificationClass ?: config('api-health.notifications.default_failed_notification');
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
