<?php

namespace ProtoneMedia\ApiHealth\Tests\TestCheckers;

use ProtoneMedia\ApiHealth\Checkers\Checker;
use ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed;

class NotificationlessChecker implements Checker
{
    public function id(): string
    {
        return md5(static::class);
    }

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
};
