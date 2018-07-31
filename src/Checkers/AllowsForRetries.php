<?php

namespace Pbmedia\ApiHealth\Checkers;

trait AllowsForRetries
{
    /**
     * Number of allowed retries.
     *
     * @var int
     */
    protected $allowedRetries;

    /**
     * Class name of the retry job.
     *
     * @var string
     */
    protected $retryCheckerJob;

    /**
     * Returns the number of allowed retries.
     *
     * @return int
     */
    public function allowedRetries(): int
    {
        return !is_null($this->allowedRetries) ? $this->allowedRetries : config('api-health.allowed_retries');
    }

    /**
     * Returns the class name of the retry job.
     *
     * @return null|string
     */
    public function retryCheckerJob():  ? string
    {
        return $this->retryCheckerJob ?: config('api-health.default_retry_checker_job');
    }

    /**
     * Callback for the retry job.
     *
     * @param  mix $job
     */
    public function withRetryJob($job)
    {

    }
}
