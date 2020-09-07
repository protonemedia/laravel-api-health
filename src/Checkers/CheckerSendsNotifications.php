<?php

namespace ProtoneMedia\ApiHealth\Checkers;

interface CheckerSendsNotifications
{
    /**
     * Number of minutes until send the failed notification again.
     *
     * @return int
     */
    public function resendFailedNotificationAfterMinutes(): int;

    /**
     * Class name of the failed notification.
     *
     * @return string
     */
    public function failedNotificationClass(): string;

    /**
     * Class name of the recovered notification.
     *
     * @return string
     */
    public function recoveredNotificationClass(): string;
}
