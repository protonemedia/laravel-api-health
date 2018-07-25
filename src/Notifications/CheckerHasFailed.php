<?php

namespace Pbmedia\ApiHealth\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed as CheckerHasFailedException;

class CheckerHasFailed extends Notification
{
    public $checker;
    public $exception;

    public function __construct(CheckerHasFailedException $exception)
    {
        $this->checker   = $exception->getChecker();
        $this->exception = $exception;
    }

    public function via(): array
    {
        return config('api-health.notifications.via');
    }

    private function data(): array
    {
        return [
            'application_name'  => config('app.name') ?: 'Your application',
            'checker_name'      => get_class($this->checker),
            'exception_message' => $this->exception->getMessage(),
        ];
    }

    public function toMail(): MailMessage
    {
        $replace = $this->data();

        return (new MailMessage)
            ->subject(trans('api-health::notifications.checker_failed_subject', $replace))
            ->line(trans('api-health::notifications.checker_failed_body', $replace));
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->from(config('api-health.notifications.slack.username'), config('api-health.notifications.slack.icon'))
            ->to(config('api-health.notifications.slack.channel'))
            ->content(trans('api-health::notifications.checker_failed_body', $this->data()));
    }
}
