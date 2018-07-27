<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerSendsNotifications
{
    public function getFailedNotificationClass(): string;

    public function getRecoveredNotificationClass(): string;
}
