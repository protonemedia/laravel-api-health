<?php

namespace Pbmedia\ApiHealth\Checkers;

interface Checker
{
    public function isSuccessful(): bool;

    public static function create();
}
