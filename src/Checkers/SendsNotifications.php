<?php

namespace Pbmedia\ApiHealth\Checkers;

trait SendsNotifications
{
    /**
     * Number of minutes until send the failed notification again.
     *
     * @var int
     */
    protected $resendFailedNotificationAfterMinutes;

    /**
     * Number of times the checker must fail in succession before
     * the first notification is sent.
     *
     * @var int
     */
    protected $onlySendFailedNotificationAfterSuccessiveFailures;

    /**
     * Class name of the failed notification.
     *
     * @var string
     */
    protected $failedNotificationClass;

    /**
     * Class name of the recovered notification.
     *
     * @var string
     */
    protected $recoveredNotificationClass;

    /**
     * Number of minutes until send the failed notification again.
     *
     * @return int
     */
    public function resendFailedNotificationAfterMinutes(): int
    {
        return $this->resendFailedNotificationAfterMinutes ?: config('api-health.notifications.resend_failed_notification_after_minutes');
    }

    /**
     * Number of times the checker must fail in succession before
     * the first notification is sent.
     *
     * @return int
     */
    public function onlySendFailedNotificationAfterSuccessiveFailures(): int
    {
        return $this->onlySendFailedNotificationAfterSuccessiveFailures ?: config('api-health.notifications.only_send_failed_notification_after_successive_failures');
    }

    /**
     * Class name of the failed notification.
     *
     * @return string
     */
    public function failedNotificationClass(): string
    {
        return $this->failedNotificationClass ?: config('api-health.notifications.default_failed_notification');
    }

    /**
     * Class name of the recovered notification.
     *
     * @return string
     */
    public function recoveredNotificationClass(): string
    {
        return $this->recoveredNotificationClass ?: config('api-health.notifications.default_recovered_notification');
    }
}
