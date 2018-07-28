<?php

namespace Pbmedia\ApiHealth;

use Pbmedia\ApiHealth\Checkers\Executor;
use Pbmedia\ApiHealth\Storage\CheckerState;

class ApiHealthChecker
{
    private $useCache = true;

    public function fresh()
    {
        $this->useCache = false;

        return $this;
    }

    public function isFailing(string $checkerClass): bool
    {
        if ($this->useCache) {
            $storage = CheckerState::make($checkerClass);

            if ($storage->exists()) {
                return $storage->isFailing();
            }
        }

        $this->useCache = true;

        return Executor::make($checkerClass)->fails();
    }

    public function isPassing(string $checkerClass): bool
    {
        return !$this->isFailing($checkerClass);
    }
}
