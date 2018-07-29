<?php

namespace Pbmedia\ApiHealth\Events;

use Pbmedia\ApiHealth\Checkers\Checker;

class CheckerHasRecovered
{
    /**
     * The checker.
     *
     * @var \Pbmedia\ApiHealth\Checkers\Checker
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
     * @param \Pbmedia\ApiHealth\Checkers\Checker  $checker
     * @param array  $failedData
     */
    public function __construct(Checker $checker, array $failedData)
    {
        $this->checker    = $checker;
        $this->failedData = $failedData;
    }
}
