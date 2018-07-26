<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Support\Collection;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use Pbmedia\ApiHealth\Notifications\CheckerHasFailed as CheckerHasFailedNotification;

class Runner
{
    private $failed;
    private $passes;

    private function checkers(): Collection
    {
        return Collection::make(config('api-health.checkers'))
            ->map(function ($checker) {
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
                return $this->failed->push([$checker, $exception]);
            }

            $this->passes->push($checker);
        });

        $this->sendNotifications();

        return $this;
    }

    private function sendNotifications()
    {
        if (empty(config('api-health.notifications.via'))) {
            return;
        }

        $notifiable = app(config('api-health.notifications.notifiable'));

        $this->failed()
            ->map(function ($checkerAndException) {
                return new CheckerHasFailedNotification($checkerAndException[0], $checkerAndException[1]);
            })
            ->each(function (CheckerHasFailedNotification $notification) use ($notifiable) {
                $notifiable->notify($notification);
            });

    }
}
