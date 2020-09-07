<?php

namespace ProtoneMedia\ApiHealth\Listeners;

use Illuminate\Notifications\Notification;

trait SendsNotifications
{
    /**
     * Send the given notification to the notifiable.
     *
     * @param  \Illuminate\Notifications\Notification $notification
     *
     * @return null|\Illuminate\Notifications\Notification
     */
    protected function sendNotification(Notification $notification)
    {
        if (empty(config('api-health.notifications.via'))) {
            return;
        }

        return tap($notification, function ($notification) {
            app(config('api-health.notifications.notifiable'))->notify($notification);
        });
    }
}
