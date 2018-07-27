<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerSendsNotifications
{
    public function getFailedNotificationClass(): string;
}
