<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckWasUnsuccessful;

class Runner
{
    private $failed;

    private $passes;

    private function checkers(): Collection
    {
        return Collection::make(Config::get('api-health.checkers'))->map(function ($checker) {
            return $checker::create();
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

        $this->checkers()->each(function (Checker $checker) {
            try {
                $checker->run();
            } catch (CheckWasUnsuccessful $exception) {
                return $this->failed->put(get_class($checker), $exception);
            }

            $this->passes->push(get_class($checker));
        });

        return $this;
    }
}
