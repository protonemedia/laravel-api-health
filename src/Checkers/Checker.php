<?php

namespace Pbmedia\ApiHealth\Checkers;

interface Checker
{
    public function run();

    public static function create();
}
