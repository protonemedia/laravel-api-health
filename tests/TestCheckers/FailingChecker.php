<?php

namespace Pbmedia\ApiHealth\Tests\TestCheckers;

use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class FailingChecker implements Checker
{
    public function run()
    {
        throw new CheckerHasFailed("TestChecker fails!");
    }

    public static function create()
    {
        return new static;
    }
};
