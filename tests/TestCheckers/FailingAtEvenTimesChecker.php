<?php

namespace Pbmedia\ApiHealth\Tests\TestCheckers;

use Illuminate\Contracts\Foundation\Application;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class FailingAtEvenTimesChecker implements Checker
{
    public function run()
    {
        static $number;

        if (is_null($number)) {
            $number = 0;
        }

        if ($number % 2 == 0) {
            $number++;
            throw new CheckerHasFailed("TestChecker fails!");
        }

        $number++;
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
