<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Pbmedia\ApiHealth\Checkers\Executor;

class Runner
{
    private $app;
    private $failed;
    private $passes;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    private function checkers(): Collection
    {
        return Collection::make(config('api-health.checkers'))->map(function ($config): Executor {
            return Executor::fromConfig($config);
        })->filter(function (Executor $executor) {
            return $executor->getChecker()->isDue($this->app);
        });
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

        $this->checkers()->each(function (Executor $executor) {
            if ($executor->failed()) {
                return $this->failed->push([
                    $executor->getChecker(),
                    $executor->getException(),
                ]);
            }

            $this->passes->push($executor->getChecker());
        });

        return $this;
    }
}
