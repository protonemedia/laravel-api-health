<?php

namespace ProtoneMedia\ApiHealth\Events;

use ProtoneMedia\ApiHealth\Checkers\Checker;
use ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed as CheckerHasFailedException;

class CheckerHasFailed
{
    /**
     * The checker.
     *
     * @var \ProtoneMedia\ApiHealth\Checkers\Checker
     */
    public $checker;

    /**
     * The exception.
     *
     * @var \ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed
     */
    public $exception;

    /**
     * The failed data.
     *
     * @var array
     */
    public $failedData;

    /**
     * Creates a new instance of this event.
     *
     * @param \ProtoneMedia\ApiHealth\Checkers\Checker  $checker
     * @param \ProtoneMedia\ApiHealth\Checkers\CheckerHasFailed  $exception
     * @param array  $failedData
     */
    public function __construct(Checker $checker, CheckerHasFailedException $exception, array $failedData)
    {
        $this->checker    = $checker;
        $this->exception  = $exception;
        $this->failedData = $failedData;
    }
}
