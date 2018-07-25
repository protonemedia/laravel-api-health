<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use Pbmedia\ApiHealth\Notifications\CheckerHasFailed as CheckerHasFailedNotification;

class Runner
{
    private $cache;
    private $config;

    private $failed;
    private $passes;

    public function __construct(CacheRepository $cache, ConfigRepository $config)
    {
        $this->cache  = $cache;
        $this->config = $config;
    }

    private function checkers(): Collection
    {
        return Collection::make($this->config->get('api-health.checkers'))->map(function ($checker) {
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
            } catch (CheckerHasFailed $exception) {
                return $this->failed->push($exception);
            }

            $this->passes->push($checker);
        });

        $this->sendNotifications();

        return $this;
    }

    private function sendNotifications()
    {
        if (empty($this->config->get('api-health.notifications.via'))) {
            return;
        }

        $this->failed->each(function (CheckerHasFailed $exception) {
            $notification = new CheckerHasFailedNotification($exception);

            $notifiable = app($this->config->get('api-health.notifications.notifiable'));

            $notifiable->notify($notification);
        });
    }
}
