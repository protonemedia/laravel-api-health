<?php

namespace Pbmedia\ApiHealth\Checkers;

use Illuminate\Notifications\Notification;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use Pbmedia\ApiHealth\Checkers\CheckerSendsNotifications;
use Pbmedia\ApiHealth\Storage\CheckerState;

class Executor
{
    /**
     * The checker.
     *
     * @var \Pbmedia\ApiHealth\Checkers\Checker
     */
    private $checker;

    /**
     * The state of the checker.
     *
     * @var \Pbmedia\ApiHealth\Storage\CheckerState
     */
    private $state;

    /**
     * The caught exception if the checker fails.
     *
     * @var \Pbmedia\ApiHealth\Checkers\CheckerHasFailed
     */
    private $exception;

    /**
     * Boolean wether the checker has failed of not.
     *
     * @var bool
     */
    private $failed;

    /**
     * The instance to be notified
     *
     * @var mixed
     */
    private $notifiable;

    /**
     * Creates an instance with the given checker
     *
     * @param \Pbmedia\ApiHealth\Checkers\Checker $checker
     */
    public function __construct(Checker $checker)
    {
        $this->checker    = $checker;
        $this->state      = new CheckerState($checker);
        $this->notifiable = app(config('api-health.notifications.notifiable'));
    }

    /**
     * Shortcut for creating an instance for a checker class.
     *
     * @param  string $checkerClass
     * @return \Pbmedia\ApiHealth\Checkers\Executor
     */
    public static function make(string $checkerClass)
    {
        return new static($checkerClass::create());
    }

    /**
     * Returns a boolean wether the checker passes.
     *
     * @return bool
     */
    public function passes(): bool
    {
        if (is_null($this->failed)) {
            $this->handle();
        }

        return !$this->failed;
    }

    /**
     * Returns a boolean wether the checker fails.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * Returns the checker.
     *
     * @return \Pbmedia\ApiHealth\Checkers\Checker
     */
    public function getChecker(): Checker
    {
        return $this->checker;
    }

    /**
     * Returns the caught exception.
     *
     * @return \Pbmedia\ApiHealth\Checkers\CheckerHasFailed
     */
    public function getException(): CheckerHasFailed
    {
        return $this->exception;
    }

    /**
     * Runs the checker, stores the state and sends the notifications if needed.
     *
     * @return $this
     */
    public function handle()
    {
        $this->failed = false;

        try {
            $this->checker->run();
            $this->handlePassingChecker();
        } catch (CheckerHasFailed $exception) {
            $this->exception = $exception;
            $this->failed    = true;
            $this->handleFailedChecker();
        }

        return $this;
    }

    /**
     * Send the given notification to the notifiable.
     *
     * @param  \Illuminate\Notifications\Notification $notification
     *
     * @return null|\Illuminate\Notifications\Notification
     */
    private function sendNotification(Notification $notification)
    {
        if (empty(config('api-health.notifications.via'))) {
            return;
        }

        return tap($notification, function ($notification) {
            $this->notifiable->notify($notification);
        });
    }

    /**
     * Handler for whenever the checker passes. Stores the state and sends
     * a recovered notification if the checker previously failed.
     *
     * @return null
     */
    private function handlePassingChecker()
    {
        $currentlyFailed = $this->state->exists() && $this->state->isFailing();

        $failedData = $currentlyFailed ? $this->state->data() : null;

        $this->state->setToPassing();

        if ($failedData && $this->checker instanceof CheckerSendsNotifications) {
            $this->sendRecoveredNotification($failedData);
        }
    }

    /**
     * Handler for whenever the checker fails. Stores the state or adds a timestamp
     * to the state if the checker previously failed, then sends
     * a notification if needed.
     *
     * @return null
     */
    private function handleFailedChecker()
    {
        if (!$this->state->exists() || $this->state->isPassing()) {
            $this->state->setToFailed($this->exception->getMessage());
        } elseif ($this->state->exists() && $this->state->isFailing()) {
            $this->state->addFailedTimestamp();
        }

        if ($this->checker instanceof CheckerSendsNotifications) {
            $this->sendFailedNotification();
        }
    }

    /**
     * Verifies that the failed the notification should be send, then
     * creates the notification and sends it to the notifiable.
     *
     * @return null
     */
    private function sendFailedNotification()
    {
        if (!$this->state->shouldSentFailedNotification()) {
            return;
        }

        $notificationClass = $this->checker->failedNotificationClass();

        $notification = $this->sendNotification(
            new $notificationClass($this->checker, $this->exception, $this->state->data())
        );

        $notification ? $this->state->markSentFailedNotification($notification) : null;
    }

    /**
     * Creates the recoved notification and sends it to the notifiable.
     *
     * @param  array $failedData
     * @return null
     */
    private function sendRecoveredNotification(array $failedData)
    {
        $notificationClass = $this->checker->recoveredNotificationClass();

        $this->sendNotification(
            new $notificationClass($this->checker, $failedData)
        );
    }
}
