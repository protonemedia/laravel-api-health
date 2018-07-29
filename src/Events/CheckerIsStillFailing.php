<?php

namespace Pbmedia\ApiHealth\Events;

use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class CheckerIsStillFailing extends CheckerHasFailed
{
    /**
     * The checker.
     *
     * @var \Pbmedia\ApiHealth\Checkers\Checker
     */
    public $checker;

    /**
     * The exception.
     *
     * @var \Pbmedia\ApiHealth\Checkers\CheckerHasFailed
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
     * @param Pbmedia\ApiHealth\Checkers\Checker  $checker
     * @param \Pbmedia\ApiHealth\Checkers\CheckerHasFailed  $exception
     * @param array  $failedData
     */
    public function __construct(Checker $checker, CheckerHasFailed $exception, array $failedData)
    {
        $this->checker    = $checker;
        $this->exception  = $exception;
        $this->failedData = $failedData;
    }
}
