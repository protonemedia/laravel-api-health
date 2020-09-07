<?php

namespace ProtoneMedia\ApiHealth\Checkers;

interface Checker
{
    /**
     * A unique identifier to store the state with.
     *
     * @return string
     */
    public function id(): string;

    /**
     * Execute the checker.
     *
     * @throws \ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed
     * @return mixed
     */
    public function run();

    /**
     * Returns a fresh configured instance of the checker.
     *
     * @return \ProtoneMedia\ApiHealth\Checkers\Checker
     */
    public static function create();
}
