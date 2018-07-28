<?php

namespace Pbmedia\ApiHealth;

use Pbmedia\ApiHealth\Checkers\Executor;
use Pbmedia\ApiHealth\Storage\CheckerState;

class ApiHealthChecker
{
    private $useCache = true;

    public function withoutCache()
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

        return Executor::make($checkerClass)->fails();
    }

    public function isPassing(string $checkerClass): bool
    {
        if ($this->useCache) {
            $storage = CheckerState::make($checkerClass);

            if ($storage->exists()) {
                return $storage->isPassing();
            }
        }

        return Executor::make($checkerClass)->passes();
    }
}
