<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Contracts\Foundation\Application;

interface Checker
{
    public function run();

    public function isDue(Application $app): bool;

    public static function create();
}
