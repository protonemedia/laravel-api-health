<?php

namespace ProtoneMedia\ApiHealth;

use Pbmedia\ApiHealth\Checkers\Executor;
use Pbmedia\ApiHealth\Storage\CheckerState;

class ApiHealthChecker
{
    /**
     * Boolean wether to use the state storage.
     *
     * @var bool
     */
    private $useStateStorage = true;

    /**
     * Disables the use of the state storage.
     *
     * @return $this
     */
    public function fresh()
    {
        $this->useStateStorage = false;

        return $this;
    }

    /**
     * Returns if the stored state is set to failed or runs the checker
     * if nothing is stored and returns wether the checker fails.
     *
     * @param  string $checkerClass
     * @return bool
     */
    public function isFailing(string $checkerClass): bool
    {
        if ($this->useStateStorage) {
            $storage = CheckerState::make($checkerClass);

            if ($storage->exists()) {
                return $storage->isFailing();
            }
        }

        $this->useStateStorage = true;

        return Executor::make($checkerClass)->fails();
    }

    /**
     * The opposite of the 'isFailing' method.
     *
     * @param  string $checkerClass
     * @return bool
     */
    public function isPassing(string $checkerClass): bool
    {
        return !$this->isFailing($checkerClass);
    }
}
