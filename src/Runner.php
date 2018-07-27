<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\Collection;
use Pbmedia\ApiHealth\Checkers\CheckerIsScheduled;
use Pbmedia\ApiHealth\Checkers\Executor;

class Runner
{
    private $failed;
    private $passes;
    private $scheduled = true;

    public function ignoreScheduling()
    {
        $this->scheduled = false;

        return $this;
    }

    public function passes(): Collection
    {
        if (!$this->passes) {
            $this->handle();
        }

        return $this->passes;
    }

    public function failed(): Collection
    {
        if (!$this->failed) {
            $this->handle();
        }

        return $this->failed;
    }

    public function handle()
    {
        $this->failed = new Collection;

        $this->passes = new Collection;

        Collection::make(config('api-health.checkers'))
            ->map(function ($checker): Executor {
                return Executor::make($checker);
            })
            ->filter(function (Executor $executor) {
                if (!$this->scheduled) {
                    return true;
                }

                if (!$executor->getChecker() instanceof CheckerIsScheduled) {
                    return true;
                }

                return $executor->getChecker()->isDue();
            })
            ->each(function (Executor $executor) {
                ($executor->fails() ? $this->failed : $this->passes)->push($executor);
            });

        return $this;
    }
}
