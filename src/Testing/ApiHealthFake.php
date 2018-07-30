<?php

namespace Pbmedia\ApiHealth\Testing;

class ApiHealthFake
{
    /**
     * An array of checkers where the value is the desired status.
     *
     * @var array
     */
    private $fakedStates = [];

    /**
     * Marks the given checker as failing.
     *
     * @param  string $checkerClass
     * @return $this
     */
    public function mustFail(string $checkerClass)
    {
        $this->fakedStates[$checkerClass] = false;

        return $this;
    }

    /**
     * Marks the given checker as passing.
     *
     * @param  string $checkerClass
     * @return $this
     */
    public function mustPass(string $checkerClass)
    {
        $this->fakedStates[$checkerClass] = true;

        return $this;
    }

    /**
     * Returns this instance.
     *
     * @return $this
     */
    public function fresh()
    {
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
        return !$this->fakedStates[$checkerClass];
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
