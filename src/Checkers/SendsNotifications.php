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
        return !is_null($this->resendFailedNotificationAfterMinutes) ? $this->resendFailedNotificationAfterMinutes : config('api-health.notifications.resend_failed_notification_after_minutes');
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
