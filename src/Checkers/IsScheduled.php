<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\EventMutex;

trait IsScheduled
{
    /**
     * Event object that is used to manages the frequency.
     *
     * @var \Illuminate\Console\Scheduling\Event
     */
    private $event;

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
