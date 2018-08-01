<?php

namespace Pbmedia\ApiHealth\Checkers;

abstract class AbstractChecker implements Checker, CheckerAllowsForRetries, CheckerIsScheduled, CheckerSendsNotifications
{
    use AllowsForRetries, IsScheduled, SendsNotifications;

    /**
     * Returns a unique identifier to store the state with.
     *
     * @return string
     */
    public function id(): string
    {
        return md5(static::class);
    }
}
