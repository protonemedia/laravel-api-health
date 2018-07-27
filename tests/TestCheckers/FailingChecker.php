<?php

namespace Pbmedia\ApiHealth\Tests\TestCheckers;

use Illuminate\Contracts\Foundation\Application;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class FailingChecker implements Checker
{
    public function run()
    {
        throw new CheckerHasFailed("TestChecker fails!");
    }

    public function isDue(Application $app): bool
    {
        return true;
    }

    public static function create()
    {
        return new static;
    }
};
