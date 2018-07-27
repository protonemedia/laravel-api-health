<?php

namespace Pbmedia\ApiHealth\Checkers;

interface Checker
{
    public function run();

    public function isDue(): bool;

    public static function create();
}
