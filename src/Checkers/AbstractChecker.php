<?php

namespace Pbmedia\ApiHealth\Checkers;

abstract class AbstractChecker implements Checker, CheckerIsScheduled, CheckerSendsNotifications
{
    use IsScheduled, SendsNotifications;
}
