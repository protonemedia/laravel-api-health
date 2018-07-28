<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerIsScheduled
{
    /**
     * Determine if the checker is due to run based on the current date.
     *
     * @return bool
     */
    public function isDue(): bool;
}
