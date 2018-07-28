<?php

namespace Pbmedia\ApiHealth\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed as CheckerHasFailedException;

class CheckerHasFailed extends Notification
{
    public $checker;
    public $exception;
    public $failedData;

    public function __construct(Checker $checker, CheckerHasFailedException $exception, array $failedData)
    {
        $this->checker    = $checker;
        $this->exception  = $exception;
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
            'failure_count'     => count($this->failedData['failed_at']),
            'failed_at'         => Carbon::createFromTimestamp($this->failedData['failed_at'][0]),
            'exception_message' => $this->exception->getMessage(),
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
            ->error()
            ->subject(trans('api-health::notifications.checker_failed_subject', $replace))
            ->line(trans('api-health::notifications.checker_failed_type', $replace))
            ->line(trans('api-health::notifications.checker_failed_at', $replace))
            ->line(trans('api-health::notifications.checker_failed_exception', $replace));
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
            ->error()
            ->from(config('api-health.notifications.slack.username'), config('api-health.notifications.slack.icon'))
            ->to(config('api-health.notifications.slack.channel'))
            ->content(implode([
                trans('api-health::notifications.checker_failed_type', $replace),
                trans('api-health::notifications.checker_failed_at', $replace),
                trans('api-health::notifications.checker_failed_exception', $replace),
            ], PHP_EOL));
    }
}
