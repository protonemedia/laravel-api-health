<?php

namespace Pbmedia\ApiHealth\Tests\TestCheckers;

use Illuminate\Contracts\Foundation\Application;
use Pbmedia\ApiHealth\Checkers\Checker;

class PassingChecker implements Checker
{
    public function run()
    {
        return;
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
