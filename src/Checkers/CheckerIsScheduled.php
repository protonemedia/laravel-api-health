<?php

namespace Pbmedia\ApiHealth\Checkers;

interface CheckerIsScheduled
{
    public function isDue(): bool;
}
