<?php

namespace ProtoneMedia\ApiHealth\Events;

use ProtoneMedia\ApiHealth\Checkers\Checker;

class CheckerHasRecovered
{
    /**
     * The checker.
     *
     * @var \ProtoneMedia\ApiHealth\Checkers\Checker
     */
    public $checker;

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
     * @param array  $failedData
     */
    public function __construct(Checker $checker, array $failedData)
    {
        $this->checker    = $checker;
        $this->failedData = $failedData;
    }
}
