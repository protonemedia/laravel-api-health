<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerAllowsForRetries
{
    /**
     * Returns the number of allowed retries.
     *
     * @return int
     */
    public function allowedRetries(): int;

    /**
     * Returns the class name of the retry job.
     *
     * @return null|string
     */
    public function retryCheckerJob():  ? string;
}
