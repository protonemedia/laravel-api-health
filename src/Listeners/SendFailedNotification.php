<?php

namespace ProtoneMedia\ApiHealth\Listeners;

use ProtoneMedia\ApiHealth\Checkers\CheckerSendsNotifications;
use ProtoneMedia\ApiHealth\Events\CheckerHasFailed;
use ProtoneMedia\ApiHealth\Storage\CheckerState;

class SendFailedNotification
{
    use SendsNotifications;

    /**
     * Sends the failed notification if needed.
     *
     * @param  \ProtoneMedia\ApiHealth\Events\CheckerHasFailed $event
     */
    public function handle($event)
    {
        $checker = $event->checker;

        if (!$checker instanceof CheckerSendsNotifications) {
            return;
        }

        $state = new CheckerState($checker);

        if (!$state->shouldSentFailedNotification()) {
            return;
        }

        $notificationClass = $checker->failedNotificationClass();

        $notification = $this->sendNotification(
            new $notificationClass($checker, $event->exception, $event->failedData)
        );

        $notification ? $state->markSentFailedNotification($notification) : null;
    }
}
