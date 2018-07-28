<?php

namespace Pbmedia\ApiHealth\Notifications;

use Illuminate\Notifications\RoutesNotifications;

class Notifiable
{
    use RoutesNotifications;

    /**
     * Returns the configured email address.
     *
     * @return null|string
     */
    public function routeNotificationForMail()
    {
        return config('api-health.notifications.mail.to');
    }

    /**
     * Returns the webhook url for slack.
     *
     * @return null|string
     */
    public function routeNotificationForSlack()
    {
        return config('api-health.notifications.slack.webhook_url');
    }

    /**
     * An identifier for this notifiable class.
     *
     * @return string
     */
    public function getKey()
    {
        return 'laravel-api-health';
    }
}
