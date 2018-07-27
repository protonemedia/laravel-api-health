<?php

namespace Pbmedia\ApiHealth\Checkers;

interface Checker
{
    public function run();

    public function shouldRun(): bool;

    public static function create();
}
