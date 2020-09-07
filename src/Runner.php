<?php

namespace ProtoneMedia\ApiHealth;

use Illuminate\Support\Collection;
use Pbmedia\ApiHealth\Checkers\CheckerIsScheduled;
use Pbmedia\ApiHealth\Checkers\Executor;

class Runner
{
    /**
     * A collection of failed checkers.
     *
     * @var \Illuminate\Support\Collection
     */
    private $failed;

    /**
     * A collection of passing checkers.
     *
     * @var \Illuminate\Support\Collection
     */
    private $passes;

    /**
     * Boolean wether to use the scheduling.
     *
     * @var bool
     */
    private $scheduled = true;

    /**
     * Collection of executors.
     *
     * @var \Illuminate\Support\Collection
     */
    private $executors;

    /**
     * Creates an instance with the given executors.
     *
     * @param array|\Illuminate\Support\Collection $executors
     */
    public function __construct($executors)
    {
        $this->executors = Collection::wrap($executors);
    }

    /**
     * Creates an instance of this class with the configured checkers.
     *
     * @return \Pbmedia\ApiHealth\Runner
     */
    public static function fromConfig(): Runner
    {
        return Collection::make(config('api-health.checkers'))
            ->map(function ($checker): Executor {
                return Executor::make($checker);
            })
            ->pipe(function ($executors) {
                return new static($executors);
            });
    }

    /**
     * Disables the scheduling.
     *
     * @return $this
     */
    public function ignoreScheduling()
    {
        $this->scheduled = false;

        return $this;
    }

    /**
     * Handles all the checkers if that has not been done yet and
     * returns the collection of passing checkers.
     *
     * @return \Illuminate\Support\Collection
     */
    public function passes(): Collection
    {
        if (!$this->passes) {
            $this->handle();
        }

        return $this->passes;
    }

    /**
     * Handles all the checkers if that has not been done yet and
     * returns the collection of failed checkers.
     *
     * @return \Illuminate\Support\Collection
     */
    public function failed(): Collection
    {
        if (!$this->failed) {
            $this->handle();
        }

        return $this->failed;
    }

    /**
     * Clears both collections and runs through all the configured checkers.
     *
     * @return $this
     */
    public function handle()
    {
        $this->failed = new Collection;

        $this->passes = new Collection;

        $this->executors->filter(function (Executor $executor) {
            if (!$this->scheduled) {
                return true;
            }

            if (!$executor->getChecker() instanceof CheckerIsScheduled) {
                return true;
            }

            return $executor->getChecker()->isDue();
        })->each(function (Executor $executor) {
            ($executor->fails() ? $this->failed : $this->passes)->push($executor);
        });

        return $this;
    }
}
