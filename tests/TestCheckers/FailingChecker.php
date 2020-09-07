<?php

namespace ProtoneMedia\ApiHealth\Tests\TestCheckers;

use ProtoneMedia\ApiHealth\Checkers\AbstractChecker;
use ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed;

class FailingChecker extends AbstractChecker
{
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
