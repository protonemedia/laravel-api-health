<?php

namespace Pbmedia\ApiHealth\Tests\TestCheckers;

use Pbmedia\ApiHealth\Checkers\Checker;

class PassingChecker implements Checker
{
    public function run()
    {
        return;
    }

    public static function create()
    {
        return new static;
    }
};
