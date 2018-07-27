<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;

abstract class AbstractChecker implements Checker
{
    protected $event;

    private function event()
    {
        if (!$this->event) {
            $eventMutex = app()->bound(EventMutex::class) ? app()->make(EventMutex::class) : app()->make(CacheEventMutex::class);

            $this->event = new Event($eventMutex, '');
        }

        return $this->event;
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
