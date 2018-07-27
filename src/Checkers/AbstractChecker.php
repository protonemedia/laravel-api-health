<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;

abstract class AbstractChecker implements Checker
{
    protected $event;

    private function event()
    {
        if (!$this->event) {
            $container = Container::getInstance();

            $eventMutex = $container->bound(EventMutex::class) ? $container->make(EventMutex::class) : $container->make(CacheEventMutex::class);

            $this->event = new Event($eventMutex, '');
        }

        return $this->event;
    }

    public function isDue(Application $app): bool
    {
        $this->schedule($this->event());

        return $this->event->isDue(app());
    }

    public function schedule(Event $event)
    {
        $event->everyMinute();
    }
}
