<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\Collection;

class Runner
{
    private $failed;
    private $passes;

    private function checkers(): Collection
    {
        return Collection::make(config('api-health.checkers'))->map(function ($config) {
            return CheckerRunner::fromConfig($config);
        })->filter(function (CheckerRunner $checkerRunner) {
            return $checkerRunner->getChecker()->shouldRun();
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

        $this->checkers()->each(function (CheckerRunner $checkerRunner) {
            if ($checkerRunner->failed()) {
                return $this->failed->push([
                    $checkerRunner->getChecker(),
                    $checkerRunner->getException(),
                ]);
            }

            $this->passes->push($checkerRunner->getChecker());
        });

        return $this;
    }
}
