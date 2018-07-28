<?php

namespace Pbmedia\ApiHealth\Checkers;

interface Checker
{
    /**
     * Execute the checker.
     *
     * @throws \Pbmedia\ApiHealth\Checkers\CheckerHasFailed
     * @return mixed
     */
    public function run();

    /**
     * Returns a fresh configured instance of the checker.
     *
     * @return \Pbmedia\ApiHealth\Checkers\Checker
     */
    public static function create();
}
