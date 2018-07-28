<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerSendsNotifications
{
    /**
     * Number of minutes until send the failed notification again.
     *
     * @return int
     */
    public function resendFailedNotificationAfterMinutes(): int;

    /**
     * Number of times the checker must fail in succession before
     * the first notification is sent.
     *
     * @return int
     */
    public function onlySendFailedNotificationAfterSuccessiveFailures(): int;

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
