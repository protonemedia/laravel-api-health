<?php

namespace Pbmedia\ApiHealth;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use Pbmedia\ApiHealth\Notifications\CheckerHasFailed as CheckerHasFailedNotification;
use Pbmedia\ApiHealth\Storage\CheckerState;

class Runner
{
    private $failed;
    private $passes;
    private $notifiable;

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
                $this->handlePassingChecker($checker);
            } catch (CheckerHasFailed $exception) {
                $this->handleFailedChecker($checker, $exception);
            }
        });

        return $this;
    }

    private function sendNotification(Notification $notification)
    {
        if (empty(config('api-health.notifications.via'))) {
            return;
        }

        if (!$this->notifiable) {
            $this->notifiable = app(config('api-health.notifications.notifiable'));
        }

        return tap($notification, function ($notification) {
            $this->notifiable->notify($notification);
        });
    }

    private function handlePassingChecker(Checker $checker)
    {
        $this->passes->push($checker);

        $state = new CheckerState($checker);

        if ($state->isFailed()) {
            $state->undoFailed();
        }
    }

    private function handleFailedChecker(Checker $checker, CheckerHasFailed $exception)
    {
        $this->failed->push([$checker, $exception]);

        $state = new CheckerState($checker);

        if (!$state->isFailed()) {
            $state->setToFailed();
        }

        if ($state->shouldSentFailedNotification()) {
            $notification = $this->sendNotification(
                new CheckerHasFailedNotification($checker, $exception)
            );

            $notification ? $state->markSentFailedNotification($notification) : null;
        }
    }
}
