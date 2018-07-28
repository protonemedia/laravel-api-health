<?php

namespace Pbmedia\ApiHealth\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Pbmedia\ApiHealth\Checkers\Checker;

class CheckerHasRecovered extends Notification
{
    public $checker;
    public $failedData;

    public function __construct(Checker $checker, array $failedData)
    {
        $this->checker    = $checker;
        $this->failedData = $failedData;
    }

    /**
     * Get the notification's channels from the configuration file.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via(): array
    {
        return config('api-health.notifications.via');
    }

    /**
     * Returns an array of all relevant data for the notification.
     *
     * @return array
     */
    protected function data(): array
    {
        return [
            'application_name'  => config('app.name') ?: 'Your application',
            'checker_type'      => get_class($this->checker),
            'failed_at'         => Carbon::createFromTimestamp($this->failedData['failed_at'][0]),
            'exception_message' => $this->failedData['exception_message'],
        ];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(): MailMessage
    {
        $replace = $this->data();

        return (new MailMessage)
            ->subject(trans('api-health::notifications.checker_recovered_subject', $replace))
            ->line(trans('api-health::notifications.checker_recovered_type', $replace))
            ->line(trans('api-health::notifications.checker_recovered_failed_at', $replace))
            ->line(trans('api-health::notifications.checker_recovered_exception', $replace));
    }

    /**
     * Build the Slack representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack(): SlackMessage
    {
        $replace = $this->data();

        return (new SlackMessage)
            ->success()
            ->from(config('api-health.notifications.slack.username'), config('api-health.notifications.slack.icon'))
            ->to(config('api-health.notifications.slack.channel'))
            ->content(implode([
                trans('api-health::notifications.checker_recovered_type', $replace),
                trans('api-health::notifications.checker_recovered_failed_at', $replace),
                trans('api-health::notifications.checker_recovered_exception', $replace),
            ], PHP_EOL));
    }
}
