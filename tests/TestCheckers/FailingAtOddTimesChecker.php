<?php

namespace ProtoneMedia\ApiHealth\Tests\TestCheckers;

use ProtoneMedia\ApiHealth\Checkers\AbstractChecker;
use ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed;

class FailingAtOddTimesChecker extends AbstractChecker
{
    public function run()
    {
        static $number;

        if (is_null($number)) {
            $number = 0;
        }

        if ($number % 2 != 0) {
            $number++;
            throw new CheckerHasFailed("TestChecker fails!");
        }

        $number++;
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
