<?php

namespace ProtoneMedia\ApiHealth\Tests\TestCheckers;

use ProtoneMedia\ApiHealth\Checkers\AbstractChecker;
use ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed;

class FailingCheckerWithJobCallback extends AbstractChecker
{
    public static $job;

    public function run()
    {
        throw new CheckerHasFailed("TestChecker fails!");
    }

    public function isDue(): bool
    {
        return true;
    }

    public static function create()
    {
        return new static;
    }

    public function withRetryJob($job)
    {
        static::$job = $job;
    }
};
