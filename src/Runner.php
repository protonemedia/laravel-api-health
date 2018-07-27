<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\Collection;
use Pbmedia\ApiHealth\Checkers\Executor;

class Runner
{
    private $failed;
    private $passes;

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
            ->map(function ($config): Executor {
                return Executor::fromConfig($config);
            })
            ->filter(function (Executor $executor) {
                return $executor->getChecker()->isDue();
            })
            ->each(function (Executor $executor) {
                ($executor->failed() ? $this->failed : $this->passes)->push($executor);
            });

        return $this;
    }
}
