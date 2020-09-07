<?php

namespace ProtoneMedia\ApiHealth\Listeners;

use Pbmedia\ApiHealth\Checkers\CheckerSendsNotifications;
use Pbmedia\ApiHealth\Events\CheckerHasRecovered;

class SendRecoveredNotification
{
    use SendsNotifications;

    /**
     * Sends the recovered notification if needed.
     *
     * @param  \Pbmedia\ApiHealth\Events\CheckerHasRecovered $event
     */
    public function handle(CheckerHasRecovered $event)
    {
        $checker = $event->checker;

        if (!$checker instanceof CheckerSendsNotifications) {
            return;
        }

        $notificationClass = $checker->recoveredNotificationClass();

        $this->sendNotification(
            new $notificationClass($checker, $event->failedData)
        );
    }
}
