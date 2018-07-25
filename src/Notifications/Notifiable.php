<?php

namespace Pbmedia\ApiHealth\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail()
    {
        return config('api-health.notifications.mail.to');
    }

    public function routeNotificationForSlack()
    {
        return config('api-health.notifications.slack.webhook_url');
    }

    public function getKey()
    {
        return 'laravel-api-health';
    }
}
