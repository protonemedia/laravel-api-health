<?php

namespace Pbmedia\ApiHealth\Checkers;

abstract class AbstractChecker implements Checker, CheckerAllowsForRetries, CheckerIsScheduled, CheckerSendsNotifications
{
    use AllowsForRetries, IsScheduled, SendsNotifications;
}
